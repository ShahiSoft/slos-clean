<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for language changes marked with lang attribute
 * WCAG 3.1.2 - Language of Parts (Level AA)
 *
 * @since 3.1.2
 */
class LanguageChangeCheck extends AbstractCheck {

	/**
	 * Common foreign phrases by language code
	 *
	 * @var array
	 */
	private $foreign_phrases = array(
		'fr' => array(
			'c\'est la vie',
			'je ne sais quoi',
			'déjà vu',
			'bon appétit',
			'raison d\'être',
			'faux pas',
			'carte blanche',
			'cul-de-sac',
			'vis-à-vis',
			'laissez-faire',
			'rendez-vous',
			'fait accompli',
			'avant-garde',
			'coup d\'état',
			'enfant terrible',
			'femme fatale',
			'haute couture',
			'joie de vivre',
			'savoir-faire',
			'tête-à-tête',
		),
		'la' => array(
			'et cetera',
			'vice versa',
			'ad hoc',
			'per se',
			'status quo',
			'de facto',
			'bona fide',
			'curriculum vitae',
			'modus operandi',
			'quid pro quo',
			'carpe diem',
			'alma mater',
			'ad nauseam',
			'in situ',
			'persona non grata',
			'pro bono',
			'verbatim',
			'veni vidi vici',
		),
		'es' => array(
			'hasta la vista',
			'gracias',
			'por favor',
			'buenos días',
			'señor',
			'señora',
			'amigo',
			'fiesta',
			'siesta',
			'macho',
			'adios',
			'hola',
		),
		'de' => array(
			'gesundheit',
			'kindergarten',
			'zeitgeist',
			'wanderlust',
			'schadenfreude',
			'doppelgänger',
			'poltergeist',
			'angst',
			'fest',
			'kaput',
			'uber',
			'wunderkind',
		),
		'it' => array(
			'ciao',
			'arrivederci',
			'buongiorno',
			'grazie',
			'cappuccino',
			'piazza',
			'pizzeria',
			'dolce vita',
			'al fresco',
			'prima donna',
			'virtuoso',
			'bravo',
		),
		'ja' => array(
			'karaoke',
			'tsunami',
			'origami',
			'samurai',
			'sensei',
			'sushi',
			'sake',
			'geisha',
			'kimono',
			'manga',
			'anime',
			'karate',
			'judo',
		),
		'zh' => array(
			'dim sum',
			'kung fu',
			'tai chi',
			'feng shui',
			'yin yang',
			'tofu',
			'wok',
		),
		'hi' => array(
			'guru',
			'karma',
			'yoga',
			'mantra',
			'nirvana',
			'avatar',
			'chakra',
			'dharma',
		),
		'ar' => array(
			'inshallah',
			'mashallah',
			'salaam',
			'harem',
			'sheikh',
		),
	);

	/**
	 * Language names for messages
	 *
	 * @var array
	 */
	private $language_names = array(
		'fr' => 'French',
		'la' => 'Latin',
		'es' => 'Spanish',
		'de' => 'German',
		'it' => 'Italian',
		'ja' => 'Japanese',
		'zh' => 'Chinese',
		'hi' => 'Hindi/Sanskrit',
		'ar' => 'Arabic',
	);

	/**
	 * Get check ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'language-change';
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Language changes within content should be marked with the lang attribute.';
	}

	/**
	 * Get severity level
	 *
	 * @return string
	 */
	public function get_severity() {
		return 'warning';
	}

	/**
	 * Get WCAG criteria
	 *
	 * @return string
	 */
	public function get_wcag_criteria() {
		return '3.1.2';
	}

	/**
	 * Run the check
	 *
	 * @param string $content HTML content to check.
	 * @return array Array of issues found.
	 */
	public function check( $content ) {
		$issues = array();
		$dom    = $this->get_dom( $content );
		$xpath  = new \DOMXPath( $dom );

		// Get document language
		$html_lang = $this->get_document_language( $dom );

		// Get all text nodes with content
		$text_nodes = $xpath->query( '//text()[normalize-space()]' );

		foreach ( $text_nodes as $text_node ) {
			$text   = strtolower( $text_node->textContent );
			$parent = $text_node->parentNode;

			// Skip script and style elements
			if ( $this->is_in_script_or_style( $parent ) ) {
				continue;
			}

			// Skip if parent or ancestor has lang attribute
			if ( $this->has_lang_attribute( $parent ) ) {
				continue;
			}

			// Check for foreign phrases
			$detected = $this->detect_foreign_phrase( $text, $html_lang );
			if ( $detected ) {
				$issues[] = array(
					'element' => $parent->tagName ?? 'text',
					'context' => $this->truncate_text( $text_node->textContent, 100 ),
					'message' => sprintf(
						'%s phrase detected ("%s"). Wrap in <span lang="%s"> for proper screen reader pronunciation.',
						$this->language_names[ $detected['lang'] ] ?? ucfirst( $detected['lang'] ),
						$detected['phrase'],
						$detected['lang']
					),
				);
			}
		}

		// Also check for elements that might need lang but don't have it
		$this->check_quote_elements( $xpath, $html_lang, $issues );

		return $issues;
	}

	/**
	 * Get document language from html or meta tag
	 *
	 * @param \DOMDocument $dom The DOM document.
	 * @return string Language code or empty string.
	 */
	private function get_document_language( $dom ) {
		$html = $dom->getElementsByTagName( 'html' );
		if ( $html->length > 0 ) {
			$lang = $html->item( 0 )->getAttribute( 'lang' );
			if ( ! empty( $lang ) ) {
				return strtolower( substr( $lang, 0, 2 ) );
			}
		}
		return '';
	}

	/**
	 * Check if element is inside script or style tag
	 *
	 * @param \DOMNode $element The element to check.
	 * @return bool True if inside script/style.
	 */
	private function is_in_script_or_style( $element ) {
		while ( $element && $element instanceof \DOMElement ) {
			$tag = strtolower( $element->tagName );
			if ( in_array( $tag, array( 'script', 'style', 'code', 'pre' ), true ) ) {
				return true;
			}
			$element = $element->parentNode;
		}
		return false;
	}

	/**
	 * Check if element or ancestor has lang attribute
	 *
	 * @param \DOMNode $element The element to check.
	 * @return bool True if has lang attribute.
	 */
	private function has_lang_attribute( $element ) {
		while ( $element && $element instanceof \DOMElement ) {
			if ( $element->hasAttribute( 'lang' ) ) {
				return true;
			}
			$element = $element->parentNode;
		}
		return false;
	}

	/**
	 * Detect foreign phrase in text
	 *
	 * @param string $text Text to search (lowercase).
	 * @param string $doc_lang Document language code.
	 * @return array|null Array with 'lang' and 'phrase' or null.
	 */
	private function detect_foreign_phrase( $text, $doc_lang ) {
		foreach ( $this->foreign_phrases as $lang_code => $phrases ) {
			// Skip if same as document language
			if ( $lang_code === $doc_lang ) {
				continue;
			}

			foreach ( $phrases as $phrase ) {
				// Use word boundary check for single words
				if ( strpos( $phrase, ' ' ) === false ) {
					// Single word - check word boundaries
					if ( preg_match( '/\b' . preg_quote( $phrase, '/' ) . '\b/i', $text ) ) {
						return array(
							'lang'   => $lang_code,
							'phrase' => $phrase,
						);
					}
				} else {
					// Multi-word phrase - direct substring match
					if ( strpos( $text, $phrase ) !== false ) {
						return array(
							'lang'   => $lang_code,
							'phrase' => $phrase,
						);
					}
				}
			}
		}
		return null;
	}

	/**
	 * Check blockquote and q elements that might contain foreign language
	 *
	 * @param \DOMXPath $xpath XPath object.
	 * @param string    $doc_lang Document language.
	 * @param array     $issues Issues array by reference.
	 */
	private function check_quote_elements( $xpath, $doc_lang, &$issues ) {
		// Check blockquote and q elements with cite attribute pointing to foreign sources
		$quotes = $xpath->query( '//blockquote[@cite] | //q[@cite]' );

		foreach ( $quotes as $quote ) {
			$cite = $quote->getAttribute( 'cite' );

			// Check for common foreign language domain patterns
			$foreign_domains = array(
				'.fr/' => 'fr',
				'.de/' => 'de',
				'.es/' => 'es',
				'.it/' => 'it',
				'.jp/' => 'ja',
				'.cn/' => 'zh',
			);

			foreach ( $foreign_domains as $domain => $lang ) {
				if ( strpos( $cite, $domain ) !== false && $lang !== $doc_lang ) {
					if ( ! $quote->hasAttribute( 'lang' ) ) {
						$issues[] = array(
							'element'  => $quote->tagName,
							'context'  => $this->get_element_html( $quote ),
							'message'  => sprintf(
								'Quote cites a %s source but has no lang attribute. If quoting in %s, add lang="%s".',
								$this->language_names[ $lang ] ?? strtoupper( $lang ),
								$this->language_names[ $lang ] ?? strtoupper( $lang ),
								$lang
							),
							'severity' => 'notice',
						);
						break;
					}
				}
			}
		}
	}

	/**
	 * Truncate text to max length
	 *
	 * @param string $text Text to truncate.
	 * @param int    $max_length Maximum length.
	 * @return string Truncated text.
	 */
	private function truncate_text( $text, $max_length ) {
		$text = trim( preg_replace( '/\s+/', ' ', $text ) );
		if ( strlen( $text ) > $max_length ) {
			return substr( $text, 0, $max_length ) . '...';
		}
		return $text;
	}
}
