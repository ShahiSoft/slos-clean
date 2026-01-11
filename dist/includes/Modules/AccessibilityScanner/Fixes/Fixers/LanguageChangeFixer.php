<?php
/**
 * Language Change Fixer
 *
 * Adds lang attribute to content in different languages.
 * WCAG 3.1.2 Language of Parts (Level AA)
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LanguageChangeFixer Class
 *
 * Detects foreign language text and wraps with appropriate lang attributes.
 */
class LanguageChangeFixer extends BaseFixer {

	/**
	 * Common foreign word patterns with their language codes.
	 * These patterns detect common words and phrases in various languages.
	 *
	 * @var array<string, array<string>>
	 */
	private const LANGUAGE_PATTERNS = array(
		// French
		'fr' => array(
			'/\b(bonjour|merci|je\s+vous|nous|avec|pour|dans|cette?|ces|une?|des|mais|qui|que|comment|pourquoi|où)\b/iu',
			'/\b(monsieur|madame|mademoiselle|café|résumé|cliché|déjà\s+vu|à\s+la\s+carte|bon\s+appétit|c\'est\s+la\s+vie|raison\s+d\'être|coup\s+de\s+grâce|je\s+ne\s+sais\s+quoi|savoir\s+faire|faux\s+pas)\b/iu',
		),
		// Spanish
		'es' => array(
			'/\b(hola|gracias|buenos?\s+días?|buenas?\s+noches?|señor|señora|por\s+favor|de\s+nada|mañana|fiesta|siesta|amigo|hasta\s+la\s+vista|que\s+será\s+será|olé)\b/iu',
			'/\b(los|las|unos|unas|con|para|por|como|pero|más|muy|también|ahora)\b/iu',
		),
		// German
		'de' => array(
			'/\b(guten\s+tag|guten\s+morgen|auf\s+wiedersehen|danke\s+schön?|bitte|herr|frau|wunderbar|zeitgeist|wanderlust|kindergarten|schadenfreude|über|angst|gesundheit|doppelgänger|poltergeist|leitmotif)\b/iu',
		),
		// Italian
		'it' => array(
			'/\b(ciao|buongiorno|grazie|prego|signor|signora|arrivederci|bella|bello|dolce\s+vita|al\s+dente|cappuccino|espresso|pasta|pizza|gelato|prima\s+donna|paparazzi|graffiti|sotto\s+voce)\b/iu',
		),
		// Portuguese
		'pt' => array(
			'/\b(obrigado|obrigada|bom\s+dia|boa\s+noite|senhor|senhora|saudade|fado)\b/iu',
		),
		// Japanese (Romaji)
		'ja' => array(
			'/\b(arigatou?|konnichiwa|sayounara|ohayou?|sumimasen|hai|iie|san|sama|chan|kun|sensei|senpai|kawaii|sugoi|anime|manga|karaoke|tsunami|emoji|origami|karate|judo|samurai|ninja|sushi|sake|tofu|ramen|wasabi|tempura|teriyaki|miso|umami|bonsai|futon|kimono|zen)\b/iu',
		),
		// Chinese (Pinyin)
		'zh' => array(
			'/\b(nihao|xiexie|zaijian|feng\s+shui|yin\s+yang|kung\s+fu|tai\s+chi|dim\s+sum|chow\s+mein|wok|ginseng|qi|chi)\b/iu',
		),
		// Latin
		'la' => array(
			'/\b(et\s+cetera|etc\.?|vice\s+versa|ad\s+hoc|per\s+se|status\s+quo|quid\s+pro\s+quo|modus\s+operandi|bona\s+fide|de\s+facto|prima\s+facie|pro\s+bono|in\s+vitro|in\s+vivo|alma\s+mater|curriculum\s+vitae|magna\s+cum\s+laude|carpe\s+diem|et\s+al\.?|i\.e\.|e\.g\.)\b/iu',
		),
		// Russian (Transliterated)
		'ru' => array(
			'/\b(spasibo|da|nyet|zdravstvuyte|dosvidaniya|glasnost|perestroika|babushka|matryoshka|bolshoi|gulag|kremlin|vodka|troika|samovar|tsar|czar)\b/iu',
		),
		// Arabic (Transliterated)
		'ar' => array(
			'/\b(salaam|shukran|marhaba|inshallah|mashallah|alhamdulillah|halal|haram|imam|muezzin|ramadan|eid|hajj|sheikh|sultan|algebra|algorithm)\b/iu',
		),
		// Hindi (Transliterated)
		'hi' => array(
			'/\b(namaste|dhanyavaad|accha|bahut|thik|haan|nahin|guru|karma|yoga|chakra|mantra|nirvana|avatar|jungle|bungalow|pundit|rajah|maharajah)\b/iu',
		),
		// Korean (Romanized)
		'ko' => array(
			'/\b(annyeonghaseyo|gamsahamnida|kimchi|taekwondo|hangul|bulgogi|bibimbap|soju|oppa|unnie|aegyo|hallyu)\b/iu',
		),
		// Greek
		'el' => array(
			'/\b(kalimera|efcharisto|yassou|opa|eureka|moussaka|gyros|souvlaki|ouzo|philosophia|demokratia)\b/iu',
		),
		// Dutch
		'nl' => array(
			'/\b(goedemorgen|dank\s+u\s+wel|alstublieft|tot\s+ziens|gezellig|appartheid|cookie|boss)\b/iu',
		),
	);

	/**
	 * Get fixer ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'language-change';
	}

	/**
	 * Get fixer description
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Adds lang attribute to content in different languages';
	}

	/**
	 * Apply language fixes to content
	 *
	 * @param string $content HTML content to fix.
	 * @return array{fixed_count: int, content: string}
	 */
	public function fix( $content ) {
		$dom   = $this->get_dom( $content );
		$xpath = new \DOMXPath( $dom );
		$fixed = 0;

		// Get page language to avoid marking same-language content.
		$page_lang = $this->get_page_language( $dom );

		// Find text-containing elements without lang attribute.
		$text_elements = $xpath->query(
			'//p[not(@lang)] | //span[not(@lang)] | //div[not(@lang)] | ' .
			'//li[not(@lang)] | //td[not(@lang)] | //th[not(@lang)] | ' .
			'//blockquote[not(@lang)] | //q[not(@lang)] | //cite[not(@lang)]'
		);

		foreach ( $text_elements as $element ) {
			$text = $element->textContent;

			// Skip if too short.
			if ( strlen( trim( $text ) ) < 3 ) {
				continue;
			}

			// Skip if ancestor already has lang attribute.
			if ( $this->has_lang_ancestor( $element ) ) {
				continue;
			}

			// Detect language.
			$detected_lang = $this->detect_language( $text );

			if ( $detected_lang && $detected_lang !== $page_lang ) {
				$element->setAttribute( 'lang', $detected_lang );
				++$fixed;
			}
		}

		// Handle inline quotes and emphasis (often contain foreign words).
		$fixed += $this->fix_inline_foreign_phrases( $dom, $xpath, $page_lang );

		// Handle specific foreign word phrases within longer text.
		$fixed += $this->fix_inline_foreign_words( $dom, $xpath, $page_lang );

		return array(
			'fixed_count' => $fixed,
			'content'     => $this->dom_to_html( $dom ),
		);
	}

	/**
	 * Detect language from text content
	 *
	 * @param string $text Text to analyze.
	 * @return string|null Detected language code or null.
	 */
	private function detect_language( $text ) {
		$text = strtolower( trim( $text ) );

		foreach ( self::LANGUAGE_PATTERNS as $lang => $patterns ) {
			foreach ( $patterns as $pattern ) {
				if ( preg_match( $pattern, $text ) ) {
					// Count matches to determine confidence.
					preg_match_all( $pattern, $text, $matches );
					$match_count = count( $matches[0] ?? array() );

					// Require at least 1 match for phrases, 2 for common words.
					// Short text (< 50 chars) only needs 1 match.
					if ( $match_count >= 1 && ( strlen( $text ) < 50 || $match_count >= 2 ) ) {
						return $lang;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Get page language from html lang attribute
	 *
	 * @param \DOMDocument $dom DOM document.
	 * @return string Language code (defaults to 'en').
	 */
	private function get_page_language( $dom ) {
		$html = $dom->getElementsByTagName( 'html' )->item( 0 );
		if ( $html && $html->hasAttribute( 'lang' ) ) {
			// Return first 2 characters (e.g., 'en' from 'en-US').
			return strtolower( substr( $html->getAttribute( 'lang' ), 0, 2 ) );
		}
		return 'en'; // Default to English.
	}

	/**
	 * Check if element has ancestor with lang attribute
	 *
	 * @param \DOMElement $element Element to check.
	 * @return bool
	 */
	private function has_lang_ancestor( $element ) {
		$parent = $element->parentNode;
		while ( $parent && $parent instanceof \DOMElement ) {
			if ( $parent->hasAttribute( 'lang' ) ) {
				return true;
			}
			$parent = $parent->parentNode;
		}
		return false;
	}

	/**
	 * Fix inline foreign phrases in em, i, cite elements
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @param string       $page_lang Page language.
	 * @return int Number of fixes applied.
	 */
	private function fix_inline_foreign_phrases( $dom, $xpath, $page_lang ) {
		$fixed = 0;

		// Find <em>, <i>, <cite> which often contain foreign words.
		$emphasis = $xpath->query( '//em[not(@lang)] | //i[not(@lang)] | //cite[not(@lang)]' );

		foreach ( $emphasis as $element ) {
			// Skip if ancestor has lang.
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
	 * Fix specific foreign words within longer text by wrapping them
	 *
	 * @param \DOMDocument $dom   DOM document.
	 * @param \DOMXPath    $xpath XPath instance.
	 * @param string       $page_lang Page language.
	 * @return int Number of fixes applied.
	 */
	private function fix_inline_foreign_words( $dom, $xpath, $page_lang ) {
		$fixed = 0;

		// Find elements with mixed language content (longer text with foreign phrases).
		$elements = $xpath->query( '//p[not(@lang)] | //li[not(@lang)] | //td[not(@lang)]' );

		foreach ( $elements as $element ) {
			// Skip if already processed or has lang ancestor.
			if ( $element->hasAttribute( 'data-slos-lang-checked' ) ) {
				continue;
			}
			if ( $this->has_lang_ancestor( $element ) ) {
				continue;
			}

			$element->setAttribute( 'data-slos-lang-checked', 'true' );

			// Look for specific known foreign phrases that should be wrapped.
			foreach ( self::LANGUAGE_PATTERNS as $lang => $patterns ) {
				if ( $lang === $page_lang ) {
					continue;
				}

				foreach ( $patterns as $pattern ) {
					$text = $element->textContent;

					if ( preg_match_all( $pattern, $text, $matches, PREG_OFFSET_CAPTURE ) ) {
						// Only wrap if the element contains mostly the page language.
						// (If entirely foreign, the main loop handles it.)
						$foreign_chars = 0;
						foreach ( $matches[0] as $match ) {
							$foreign_chars += strlen( $match[0] );
						}

						// If foreign content is less than 50% of total, wrap individual phrases.
						if ( $foreign_chars < strlen( $text ) * 0.5 ) {
							// Mark as needing JS-based wrapping (complex DOM manipulation).
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
