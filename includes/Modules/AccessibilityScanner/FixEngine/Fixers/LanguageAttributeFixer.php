<?php
/**
 * Language Attribute Fixer
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LanguageAttributeFixer
 * 
 * Ensures proper language attributes on content.
 */
final class LanguageAttributeFixer extends AbstractFixer {

	public function get_id(): string {
		return 'language_attribute';
	}

	public function get_name(): string {
		return __( 'Language Attributes', 'shahi-legalflowsuite' );
	}

	public function get_description(): string {
		return __( 'Adds or fixes language attributes on HTML elements and identifies content in different languages.', 'shahi-legalflowsuite' );
	}

	public function get_wcag_criteria(): array {
		return [ '3.1.1', '3.1.2' ];
	}

	public function get_category(): string {
		return 'content';
	}

	public function can_fix( string $content ): bool {
		// Can always check for language issues
		return true;
	}

	protected function apply_fix( string $content, array $options = [] ): FixResult {
		$doc = $this->parse_html( $content );
		
		if ( ! $doc ) {
			return FixResult::error( $this->get_id(), 'Failed to parse HTML', $content );
		}

		$fixes_applied = 0;
		$details = [];

		// Get site language
		$site_lang = get_bloginfo( 'language' ) ?: 'en-US';
		$site_lang_short = explode( '-', $site_lang )[0];

		// Check for html element with missing lang
		$html_elements = $this->query( '//html[not(@lang)]' );
		foreach ( $html_elements as $html ) {
			$html->setAttribute( 'lang', $site_lang );
			$fixes_applied++;
			$details[] = [
				'element' => 'html',
				'action'  => 'added_lang',
				'lang'    => $site_lang,
			];
		}

		// Check for elements with lang attribute that is empty
		$empty_lang = $this->query( '//*[@lang=""]' );
		foreach ( $empty_lang as $element ) {
			$element->setAttribute( 'lang', $site_lang );
			$fixes_applied++;
			$details[] = [
				'element' => $element->nodeName,
				'action'  => 'fixed_empty_lang',
				'lang'    => $site_lang,
			];
		}

		// Look for common foreign language patterns and add lang attribute
		$foreign_patterns = $this->detect_foreign_language_content( $site_lang_short );
		
		if ( ! empty( $foreign_patterns ) ) {
			// Find blockquotes or q elements that might be foreign quotes
			$quotes = $this->query( '//blockquote[not(@lang)] | //q[not(@lang)]' );
			
			foreach ( $quotes as $quote ) {
				$text = $quote->textContent;
				$detected_lang = $this->detect_language( $text, $site_lang_short );
				
				if ( $detected_lang && $detected_lang !== $site_lang_short ) {
					$quote->setAttribute( 'lang', $detected_lang );
					$fixes_applied++;
					$details[] = [
						'element'       => $quote->nodeName,
						'action'        => 'detected_language',
						'lang'          => $detected_lang,
						'text_preview'  => substr( $text, 0, 50 ),
					];
				}
			}
		}

		if ( $fixes_applied === 0 ) {
			return FixResult::skipped( $this->get_id(), 'No language attribute fixes needed', $content );
		}

		return FixResult::success(
			$this->get_id(),
			$fixes_applied,
			$content,
			$this->get_html(),
			$details
		);
	}

	/**
	 * Get patterns for detecting foreign language content
	 *
	 * @param string $site_lang
	 * @return array
	 */
	private function detect_foreign_language_content( string $site_lang ): array {
		$patterns = [
			'fr' => [
				'pattern' => '/\b(le|la|les|de|du|des|un|une|et|est|que|qui|dans|pour|sur|avec|ce|se|ne|pas|son|il|elle|nous|vous|ils|elles|être|avoir|faire)\b/i',
				'min_matches' => 3,
			],
			'de' => [
				'pattern' => '/\b(der|die|das|und|in|zu|den|ist|von|mit|sich|des|auf|für|nicht|ein|eine|als|auch|es|an|werden|aus|er|hat|dass)\b/i',
				'min_matches' => 3,
			],
			'es' => [
				'pattern' => '/\b(el|la|los|las|de|del|en|un|una|que|es|por|con|para|no|se|su|al|lo|como|más|pero|sus|le|ya|o|este|ha|sí|porque)\b/i',
				'min_matches' => 3,
			],
			'it' => [
				'pattern' => '/\b(il|la|i|gli|le|di|da|in|su|per|con|non|che|è|un|una|sono|si|del|della|dei|delle|al|alla|ai|alle)\b/i',
				'min_matches' => 3,
			],
			'pt' => [
				'pattern' => '/\b(o|a|os|as|de|da|do|em|um|uma|que|é|para|com|não|se|na|no|por|mais|como|mas|foi|ao|ele|ela|seu|sua)\b/i',
				'min_matches' => 3,
			],
		];

		// Remove site language from detection
		unset( $patterns[ $site_lang ] );

		return $patterns;
	}

	/**
	 * Detect language of text
	 *
	 * @param string $text
	 * @param string $exclude_lang
	 * @return string|null
	 */
	private function detect_language( string $text, string $exclude_lang ): ?string {
		$patterns = $this->detect_foreign_language_content( $exclude_lang );
		
		$best_match = null;
		$best_score = 0;

		foreach ( $patterns as $lang => $config ) {
			if ( preg_match_all( $config['pattern'], $text, $matches ) ) {
				$match_count = count( $matches[0] );
				$word_count = str_word_count( $text );
				
				if ( $word_count > 0 ) {
					$score = $match_count / $word_count;
					
					if ( $match_count >= $config['min_matches'] && $score > $best_score ) {
						$best_score = $score;
						$best_match = $lang;
					}
				}
			}
		}

		// Only return if confidence is high enough
		return $best_score > 0.15 ? $best_match : null;
	}
}
