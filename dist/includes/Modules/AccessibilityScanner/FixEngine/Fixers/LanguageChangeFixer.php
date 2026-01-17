<?php

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Fixers;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\AbstractFixer;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\FixResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LanguageChangeFixer extends AbstractFixer {

	/**
	 * Common foreign word patterns with their language codes.
	 *
	 * @var array<string, array<string>>
	 */
	private const LANGUAGE_PATTERNS = array(
		// French...
		'fr' => array(
			'/\b(bonjour|merci|je\s+vous|nous|avec|pour|dans|cette?|ces|une?|des|mais|qui|que|comment|pourquoi|où)\b/iu',
			'/\b(monsieur|madame|mademoiselle|café|résumé|cliché|déjà\s+vu|à\s+la\s+carte|bon\s+appétit|c\'est\s+la\s+vie|raison\s+d\'être|coup\s+de\s+grâce|je\s+ne\s+sais\s+quoi|savoir\s+faire|faux\s+pas)\b/iu',
		),
		// Spanish...
		'es' => array(
			'/\b(hola|gracias|buenos?\s+días?|buenas?\s+noches?|señor|señora|por\s+favor|de\s+nada|mañana|fiesta|siesta|amigo|hasta\s+la\s+vista|que\s+será\s+será|olé)\b/iu',
			'/\b(los|las|unos|unas|con|para|por|como|pero|más|muy|también|ahora)\b/iu',
		),
		// German...
		'de' => array(
			'/\b(guten\s+tag|guten\s+morgen|auf\s+wiedersehen|danke\s+schön?|bitte|herr|frau|wunderbar|zeitgeist|wanderlust|kindergarten|schadenfreude|über|angst|gesundheit|doppelgänger|poltergeist|leitmotif)\b/iu',
		),
		// Italian...
		'it' => array(
			'/\b(ciao|buongiorno|grazie|prego|signor|signora|arrivederci|bella|bello|dolce\s+vita|al\s+dente|cappuccino|espresso|pasta|pizza|gelato|prima\s+donna|paparazzi|graffiti|sotto\s+voce)\b/iu',
		),
		// Portuguese...
		'pt' => array(
			'/\b(obrigado|obrigada|bom\s+dia|boa\s+noite|senhor|senhora|saudade|fado)\b/iu',
		),
		// Japanese (Romaji)...
		'ja' => array(
			'/\b(arigatou?|konnichiwa|sayounara|ohayou?|sumimasen|hai|iie|san|sama|chan|kun|sensei|senpai|kawaii|sugoi|anime|manga|karaoke|tsunami|emoji|origami|karate|judo|samurai|ninja|sushi|sake|tofu|ramen|wasabi|tempura|teriyaki|miso|umami|bonsai|futon|kimono|zen)\b/iu',
		),
		// Chinese (Pinyin)...
		'zh' => array(
			'/\b(nihao|xiexie|zaijian|feng\s+shui|yin\s+yang|kung\s+fu|tai\s+chi|dim\s+sum|chow\s+mein|wok|ginseng|qi|chi)\b/iu',
		),
		// Latin...
		'la' => array(
			'/\b(et\s+cetera|etc\.?|vice\s+versa|ad\s+hoc|per\s+se|status\s+quo|quid\s+pro\s+quo|modus\s+operandi|bona\s+fide|de\s+facto|prima\s+facie|pro\s+bono|in\s+vitro|in\s+vivo|alma\s+mater|curriculum\s+vitae|magna\s+cum\s+laude|carpe\s+diem|et\s+al\.?|i\.e\.|e\.g\.)\b/iu',
		),
		// Russian (Transliterated)...
		'ru' => array(
			'/\b(spasibo|da|nyet|zdravstvuyte|dosvidaniya|glasnost|perestroika|babushka|matryoshka|bolshoi|gulag|kremlin|vodka|troika|samovar|tsar|czar)\b/iu',
		),
		// Arabic (Transliterated)...
		'ar' => array(
			'/\b(salaam|shukran|marhaba|inshallah|mashallah|alhamdulillah|halal|haram|imam|muezzin|ramadan|eid|hajj|sheikh|sultan|algebra|algorithm)\b/iu',
		),
		// Hindi (Transliterated)...
		'hi' => array(
			'/\b(namaste|dhanyavaad|accha|bahut|thik|haan|nahin|guru|karma|yoga|chakra|mantra|nirvana|avatar|jungle|bungalow|pundit|rajah|maharajah)\b/iu',
		),
		// Korean (Romanized)...
		'ko' => array(
			'/\b(annyeonghaseyo|gamsahamnida|kimchi|taekwondo|hangul|bulgogi|bibimbap|soju|oppa|unnie|aegyo|hallyu)\b/iu',
		),
		// Greek...
		'el' => array(
			'/\b(kalimera|efcharisto|yassou|opa|eureka|moussaka|gyros|souvlaki|ouzo|philosophia|demokratia)\b/iu',
		),
		// Dutch...
		'nl' => array(
			'/\b(goedemorgen|dank\s+u\s+wel|alstublieft|tot\s+ziens|gezellig|appartheid|cookie|boss)\b/iu',
		),
	);

	public function get_id(): string {
		return 'language-change';
	}

	protected function apply_fix( string $content, array $options = array() ): FixResult {
		$dom = $this->parse_html( $content );
		if ( ! $dom instanceof \DOMDocument ) {
			return FixResult::skipped( $this->get_id(), 'Unable to parse HTML', $content );
		}

		$xpath       = new \DOMXPath( $dom );
		$fixed_count = 0;

		$page_lang = $this->get_page_language( $dom );

		$text_elements = $xpath->query(
			'//p[not(@lang)] | //span[not(@lang)] | //div[not(@lang)] | '
			. '//li[not(@lang)] | //td[not(@lang)] | //th[not(@lang)] | '
			. '//blockquote[not(@lang)] | //q[not(@lang)] | //cite[not(@lang)]'
		);

		if ( $text_elements instanceof \DOMNodeList ) {
			foreach ( $text_elements as $element ) {
				if ( ! $element instanceof \DOMElement ) {
					continue;
				}

				$text = $element->textContent;

				if ( strlen( trim( $text ) ) < 3 ) {
					continue;
				}

				if ( $this->has_lang_ancestor( $element ) ) {
					continue;
				}

				$detected_lang = $this->detect_language( $text );

				if ( $detected_lang && $detected_lang !== $page_lang ) {
					$element->setAttribute( 'lang', $detected_lang );
					++$fixed_count;
				}
			}
		}

		$fixed_count += $this->fix_inline_foreign_phrases( $dom, $xpath, $page_lang );
		$fixed_count += $this->fix_inline_foreign_words( $dom, $xpath, $page_lang );

		if ( $fixed_count <= 0 ) {
			return FixResult::skipped( $this->get_id(), 'No language-change fixes applied', $content );
		}

		$fixed_content = $this->get_html( $dom );

		return FixResult::success(
			$this->get_id(),
			$fixed_count,
			$content,
			$fixed_content,
			array()
		);
	}

	/**
	 * Detect language from text content.
	 */
	private function detect_language( string $text ): ?string {
		$text = strtolower( trim( $text ) );

		foreach ( self::LANGUAGE_PATTERNS as $lang => $patterns ) {
			foreach ( $patterns as $pattern ) {
				if ( preg_match( $pattern, $text ) ) {
					preg_match_all( $pattern, $text, $matches );
					$match_count = count( $matches[0] ?? array() );

					if ( $match_count >= 1 && ( strlen( $text ) < 50 || $match_count >= 2 ) ) {
						return $lang;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Get page language from html lang attribute.
	 */
	private function get_page_language( \DOMDocument $dom ): string {
		$html = $dom->getElementsByTagName( 'html' )->item( 0 );
		if ( $html instanceof \DOMElement && $html->hasAttribute( 'lang' ) ) {
			return strtolower( substr( $html->getAttribute( 'lang' ), 0, 2 ) );
		}
		return 'en';
	}

	/**
	 * Check if element has ancestor with lang attribute.
	 */
	private function has_lang_ancestor( \DOMElement $element ): bool {
		$parent = $element->parentNode;
		while ( $parent instanceof \DOMElement ) {
			if ( $parent->hasAttribute( 'lang' ) ) {
				return true;
			}
			$parent = $parent->parentNode;
		}
		return false;
	}

	/**
	 * Fix inline foreign phrases in em, i, cite elements.
	 */
	private function fix_inline_foreign_phrases( \DOMDocument $dom, \DOMXPath $xpath, string $page_lang ): int {
		$fixed = 0;

		$emphasis = $xpath->query( '//em[not(@lang)] | //i[not(@lang)] | //cite[not(@lang)]' );

		if ( ! $emphasis instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $emphasis as $element ) {
			if ( ! $element instanceof \DOMElement ) {
				continue;
			}

			if ( $this->has_lang_ancestor( $element ) ) {
				continue;
			}

			$text     = $element->textContent;
			$detected = $this->detect_language( $text );

			if ( $detected && $detected !== $page_lang ) {
				$element->setAttribute( 'lang', $detected );
				++$fixed;
			}
		}

		return $fixed;
	}

	/**
	 * Fix specific foreign words within longer text by wrapping them.
	 */
	private function fix_inline_foreign_words( \DOMDocument $dom, \DOMXPath $xpath, string $page_lang ): int {
		$fixed = 0;

		$elements = $xpath->query( '//p[not(@lang)] | //li[not(@lang)] | //td[not(@lang)]' );

		if ( ! $elements instanceof \DOMNodeList ) {
			return 0;
		}

		foreach ( $elements as $element ) {
			if ( ! $element instanceof \DOMElement ) {
				continue;
			}

			if ( $element->hasAttribute( 'data-slos-lang-checked' ) ) {
				continue;
			}
			if ( $this->has_lang_ancestor( $element ) ) {
				continue;
			}

			$element->setAttribute( 'data-slos-lang-checked', 'true' );

			foreach ( self::LANGUAGE_PATTERNS as $lang => $patterns ) {
				if ( $lang === $page_lang ) {
					continue;
				}

				foreach ( $patterns as $pattern ) {
					$text = $element->textContent;

					if ( preg_match_all( $pattern, $text, $matches, PREG_OFFSET_CAPTURE ) ) {
						$foreign_chars = 0;
						foreach ( $matches[0] as $match ) {
							$foreign_chars += strlen( $match[0] );
						}

						if ( $foreign_chars < strlen( $text ) * 0.5 ) {
							if ( ! $element->hasAttribute( 'data-slos-has-foreign' ) ) {
								$element->setAttribute( 'data-slos-has-foreign', $lang );
								++$fixed;
							}
						}
					}
				}
			}
		}

		return $fixed;
	}
}
