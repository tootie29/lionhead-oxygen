<?php
/*
Plugin Name:  MA Custom Fonts
Description:  Load custom fonts and inject to Oxygen and Bricks
Author:       <a href="https://www.altmann.de/">Matthias Altmann</a>
Project:      Code Snippet: Load custom fonts and inject to Oxygen and Bricks
Version:      3.3.1
Plugin URI:   https://www.altmann.de/en/blog-en/code-snippet-custom-fonts/
Description:  en: https://www.altmann.de/en/blog-en/code-snippet-custom-fonts/
              de: https://www.altmann.de/blog/code-snippet-eigene-schriftarten/
Copyright:    © 2020-2022, Matthias Altmann

Version History:
Date		Version		Description
--------------------------------------------------------------------------------------------------------------
2022-10-17	3.3.1		Changes:
						- Migrated JavaScript from jQuery to vanilla JS (ES6) to eliminate jQuery dependency.
						- Modernized initialization to avoid errors with WPCodeBox:
						  Replaced @ error control operator with ?? null coalescing operator
						  (Thanks to Alexander van Aken for reporting)
2022-09-26	3.3.0		New Features:
						- Custom Fonts are now also available in Bricks Builder. They are listed in section
						  "Standard Fonts".
						  (Thanks to Luke Wakefield for the implementation idea and to Tom Homer for contacting
						  and informing me about Luke's solution)
						- New configuration option $fonts_in_gutenberg:
						  Allows to enable (default) or disable the assignment of custom fonts in Gutenberg. 
						Changes:
						- Renamed code snippet from "MA Oxygen Custom Fonts" to "MA Custom Fonts"
						Fixes:
						- The snippet applies display and text fonts defined in Oxygen to Gutenberg Editor. 
						  Google fonts defined in Oxygen are not loaded for Gutenberg. 
						  So we can't assign Google fonts to Gutenberg. Only custom fonts are now assigned.
						  (Thanks to Kamil Alhaijali for reporting) 
2022-09-19	3.2.7		Compatibility Fix:
						- In Oxygen 4.0.4, Pro Menu calls ECF_Plugin::get_font_families() as Ajax call. 
						  For performance reasons, MA Custom Fonts doesn't get initialized for Ajax calls and 
						  doesn't have the base font directory set, which causes an error. We now just return 
						  an empty custom fonts list to prevent this error. That doesn't impact the Pro Menu 
						  functionality. 
						  (Many thanks to Kevin Pudlo from the Oxygen development team for reporting, support 
						  and testing concerning the Pro Menu component, and Alexander van Aken for reporting 
						  a similar issue concerning the Mega Menu component.)
2022-06-02	3.2.6		New Features:
						- New configuration option $wfl_support_woff: 
						  Font packages downloaded from Web Font Loader (https://webfontloader.altmann.de) 
						  contain WOFF2 files supported by all modern browsers, and also WOFF files to support 
						  ancient browsers before 2016 like Internet Explorer, or Safari on older Apple devices.
						  The new default setting is to NOT provide old style WOFF files for ancient browsers.
						  Set this option to true if you still need to support old browsers before 2016.
						  (Many thanks to Sunny Trochaniak and Yan Kiara for reporting issues and supporting 
						  investigations with unexpected WOFF loading when using symbols/emoji)
						- Added display of timing details at end of page Appearance > MA Custom Fonts
						Fixes:
						- Corrected initialization of dummy ECF plugin to let Oxygen detect custom fonts and 
						  prevents attempts to be loaded as Google fonts
						  (Thanks to Firat Sekerli for reporting)
						Changes:
						- Removed configuration and all code for debugging.
						- For Web Font Loader fonts: Only emit CSS if related font file exists. 
						  User might have deleted e.g. the files for a specific language.
2022-03-13	3.2.5		New Features:
						- Legend and coloring for font formats in custom fonts test screen
						Changes:
						- Renamed Admin menu Appearance > "Custom Fonts" to "MA Custom Fonts"
						- Renamed test shortcode from "maltmann_custom_fonts_test" to "ma-customfonts-test"
						- Optimized handling of font_base (dir, url) by class var
						- Enhanced detection of variable weight fonts by [wght] in filename
						  (Thanks to Paul Batey for reporting)
						- Adapted min/max font weight for variable fonts from 100/900 to 1/1000 according to 
						  https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-weight#values
						  (Thanks to Paul Batey for reporting)
						- Completely re-built custom font test screen (WP Admin and Shortcode) to improve 
							- variable weight fonts (display logic)
							- responsive view (for test on smartsphones)
2022-02-11				Tested with PHP 8.0, WordPress 5.9, Oxygen 4.0 beta 1
2021-10-26	3.2.4		Changes:
						- Deferred initialization to hook wp_loaded, after incompatibility checks
						- Gutenberg: Apply custom fonts for new posts also
						- Renamed IDs (for scripts and styles) and CSS classes (for font test) for consistency
						Performance:
						- Avoid unneccessary init by separating admin/frontend and more detailed checks 
						Fixes:
						- Fixed an issue comparing hashes for code and file
						  (Thanks to David Sitniansky for reporting)
						- Emit styles for Gutenberg correctly if $cssoutput is configured as 'html'
2021-10-15	3.2.3		Changes:
						- Gutenberg: Use display font for post title
						  (Thanks to Sunny Trochaniak for reporting)
						- Fonts preview: Changed sample text size from 15 to 16 px which is browser standard 
						- Fonts preview: Shortcode output uses WP system fonts for UI instead of custom fonts
						- CSS file link now contains ?ver=... instead of ?hash only
						Performance: 
						- Only create CSS file if contents (font configuration) have changed 
						Fixes:
						- Removed itemprop="stylesheet" from <link rel="stylesheet" ...>
						  (Thanks to Max Gottschalk for reporting and testing)
						- Proper quoting for font families
2021-08-02	3.2.2		Changes:
						- Using scheme-less URL to avoid issues with wrong WordPress URL configuration
						- Added admin notice if folder wp-content/uploads/fonts is not writable.
						Fixes:
						- Fixed issue with uppercase font file extensions.
2021-06-18	3.2.1		Fixes: 
						- Fixed typo in CSS for Gutenberg
2021-06-18	3.2.0		New Features:
						- Display Custom Fonts in Gutenberg (enqueue ma_customfonts.css for font definitions, 
						  add custom style for display and text font from Oxygen global settings)
						Changes:
						- Auto-create folder /wp-content/uploads/fonts
2021-05-17	3.1.3		Changes:
						- Optimized init sequence
						- Emit implementation and version in CSS
						- Reversed Version History order
2021-05-16	3.1.2		Changes:
						- Avoid font swap: Load ma-customfonts.css early; default font-display now "block"
						New Features:
						- Allow space in addition to dashes to detect font weights and styles
						  (Thanks to Henning Wechsler for reporting)
2021-03-21	3.1.1		Fixes:
						- Fixed font loading in Gutenberg editor (with Oxygen Gutenberg Integration)
2021-03-20	3.1.0		New Features:
						- "Oblique" in font file name is now detected as italic style
						- Custom Fonts test: Option to show font weights/styles without files as browser would 
						  simulate. 
						Changes:
						- Output Custom Font CSS in head instead of footer to prevent font swap
						- Custom Fonts test: Changed logic for output font samples and related file info
						Fixes:
						- Custom Fonts test: Fixed font file count for fonts provided by Web Font Loader
2021-03-08	3.0.2		Fix:
						- Compatibility with Windows server and local dev environments.
						  (Thanks to Franz Müller for reporting and testing)
2021-02-23	3.0.1		Fixes:
						- Compatibility with WordPress 5.6.2 (doesn't set REQUEST::action anymore)
						- Compatibility check with Swiss Knife's Font Manager feature
						- Compatibility with Swiss Knife (font lists did not display custom fonts light blue)
2021-02-18	3.0.0		New Features:
						- Support for font packages from Web Font Loader (https://webfontloader.altmann.de/)
						- New configuration option: CSS output as inline CSS or external CSS file (cacheable)
						- New configuration option: CSS minimize (was controlled by debug switch before)
						- Changed configuration option: font-display may now be specified as desired, 
						  default is now 'auto'
2021-01-24 	2.5.2		New Features:
						- Custom Fonts test (via Admin panel and shortcode) now allows custom sample text
2021-01-23	2.5.1		Fix:
						- Changed compatibility check process: 
						  Changed Hook for plugin compatibility check from plugins_loaded to init
						  Check only if admin and function is_plugin_active exists
						  (Thanks to Sebastian Albert for reporting and testing)
2021-01-23	2.5.0		New features:
						- WP Admin Menu: Appearance > Custom Fonts 
						  Shows a list of all registered custom fonts, including samples, weights, formats
						  with adaptable sample font size 
						- Detect font weight terms "Book" (400) and "Demi" (600) 
						Changes:
						- Redesign of classes (MA_CustomFonts, ECF_Plugin)
						- Font swap is now a configuration option
						- Cut "-webfont" from font name
2020-12-08	2.2.5		Changes:
						- In CSS, font sources are now listed in a prioritized order (eot,woff2,woff,ttf,otf,svg)
						  (Thanks to Viorel Cosmin Miron for reporting)
						- Test shortcode now also displays available font formats
2020-11-27	2.2.4		Fix:
						- Corrected typo in variable name (2 occurrences) that could cause repeated search 
						  for font files. (Thanks to Viorel Cosmin Miron for reporting)
2020-11-25	2.2.3		Changes:
						- In Oxygen font selectors the custom fonts are now displayed in lightblue 
						  to distinguish from default, websafe and Google Fonts 
2020-11-25	2.2.2		New features:
						- Partial support for fonts with variable weights, detected by "VariableFont" in 
						  filename. CSS output as font-weight:100 900;
2020-11-24	2.2.1		New features:
						- Shortcode [ maltmann_custom_font_test ] for listing all custom fonts with their weights 
						  and styles
						Changes:
						- Fonts are now sorted alphabetically for e.g. CSS output
						- Added more request rules to skipping code execution when not needed
2020-11-23	2.2.0		New features:
						- Detection of font weight from number values 
						- CSS now contains font-display:swap;
2020-10-03 	2.1.1		Fix:
						- Handle empty fonts folder correctly. 
						  (Thanks to Mario Peischl for reporting)
						- Corrected title and file name (typo "cutsom") of Code Snippet
2020-09-16	2.1.0		New features:
						- Detection of font weight and style from file name
						Fixes:
						- EOT: Typo in extension detection
						- EOT: Missing quote in style output
2020-09-15	2.0.0		Improved version
						- Finds all font files (eot, otf, svg, ttf, woff, woff2) in directory wp-content/uploads/fonts/
						- Optionally recursive
						- Takes font name from file name
						- Emits optimized CSS with alternative font formats
						- Special handling for EOT for Internet Explorer
2020-04-10	1.0.0		Initial Release for customer project
--------------------------------------------------------------------------------------------------------------
*/



if (!class_exists('MA_CustomFonts')) :
class MA_CustomFonts {

	const TITLE     	= 'MA Custom Fonts';
	const VERSION   	= '3.3.1';

	// ===== CONFIGURATION =====
	public static $recursive 			= true; 	// enables recursive file scan
	public static $parsename 			= true; 	// enables parsing font weight and style from file name
	public static $fontdisplay			= 'swap';	// set font-display to auto, block, swap, fallback, optional or '' (disable)
	public static $cssoutput			= 'file';	// 'file': Create a CSS file (cacheable by browser). 'html': Inline CSS
	public static $cssminimize			= true; 	// minimize CSS (true) or pretty print (false)
	public static $wfl_support_woff		= false;	// support old style WOFF files from Web Font Loader for browsers before 2016
	public static $fonts_in_gutenberg	= true;		// assign custom fonts defined in Oxygen to Gutenberg editor
	public static $timing				= false; 	// write timing info (a lot!) to wordpress debug.log if WP_DEBUG enabled		
	public static $sample_text 			= 'The quick brown fox jumps over the lazy dog.';	

	// ===== INTERNAL. DO NOT EDIT. =====
	public 	static $prioritized_formats	= ['eot','woff2','woff','ttf','otf','svg'];
	public 	static $var_weight_formats	= ['woff2','ttf'];
	private static $fonts_base			= null; // will be initialized automatically
	private static $fonts 				= null;	// will be populated with fonts and related files we found
	private static $fonts_details_cache	= [];	// cache for already parsed font details
	private static $font_files_cnt		= 0;	// number of font files parsed
	private static $font_css			= null;	// temp storage for custom font css
	private static $guten_oxy_font_css	= null;	// temp storage for Gutenberg css defining oxygen fonts
	private static $timing_fonts_collect= null; // timing for fonts collection
	private static $timing_fonts_css	= null; // timing for fonts CSS generation
	
	//-------------------------------------------------------------------------------------------------------------------
	static function init() {

		if (!defined('MA_CustomFonts_Version')) define('MA_CustomFonts_Version',self::VERSION);


		// set up fonts dir and url
		self::$fonts_base = self::get_fonts_base(); 
		if (!self::$fonts_base) 	{return;}

		// Pre-fill font definitions
		$st_fonts_collect = microtime(true);
		$custom_fonts = self::get_font_families();
		self::$timing_fonts_collect = microtime(true) - $st_fonts_collect;
		// Pre-fill custom font css, and optionally write file
		$st_fonts_css = microtime(true);
		self::$font_css = self::get_font_css();
		self::$timing_fonts_css = microtime(true) - $st_fonts_css;

		// Frontend: Emit custom font css in head 
		add_action( 'wp_head', function(){ 
			echo self::$font_css; 
		},5);

		// Backend
		if (is_admin()) {
			// Load CSS for calls using Gutenberg Editor. 
			// Requires ma_customfont.css (for font loading) and some Oxygen settings (for font assignment)
			global $pagenow;
			if ( ($pagenow === 'post-new.php') || ($pagenow === 'post.php' && ($_REQUEST['action']??'') === 'edit') ) {
				
				// enqueue or embed ma_customfonts.css
				if (self::$cssoutput == 'file') {
					wp_enqueue_style('ma-customfonts', self::$fonts_base->url.'/ma_customfonts.css'); 
				} else {
					$plain_css = self::get_font_css(true); 
					add_action( 'admin_head', function(){ 
						echo self::get_font_css(); 
					},5);
				}

				if (defined('CT_VERSION') && self::$fonts_in_gutenberg) { // Oxygen installed and active?
					// create custom style for body, h1-h6 from Oxygen global settings
					// Google fonts are not loaded in Gutenberg. We only set custom style for custom fonts.
					$ct_global_settings = ct_get_global_settings();
					self::$guten_oxy_font_css = '';
					// text font
					if (in_array($ct_global_settings['fonts']['Text'],$custom_fonts)) {
						self::$guten_oxy_font_css .= sprintf('body .editor-styles-wrapper {font-family:%s;}',self::quote_font_names($ct_global_settings['fonts']['Text']));
					}
					// heading font
					if (in_array($ct_global_settings['fonts']['Display'],$custom_fonts)) {
						self::$guten_oxy_font_css .= sprintf('body .editor-styles-wrapper :is(h1,h2,h3,h4,h5,h6,.editor-post-title) {font-family:%s;}',self::quote_font_names($ct_global_settings['fonts']['Display']));
					}
					if (self::$guten_oxy_font_css) {
						// add custom style to overwrite Gutenberg's default font
						if (self::$cssoutput == 'file') {
							wp_add_inline_style('ma-customfonts',self::$guten_oxy_font_css); 
						} else {
							add_action( 'admin_head', function(){ 
								echo '<style id="ma-customfonts-gutenberg">'.self::$guten_oxy_font_css.'</style>'; 
							},5);
						}
					}
				}
			}
		}

		// Shortcode for testing custom fonts (listing all fonts with their formats, weights, styles)
		add_shortcode('ma-customfonts-test', function( $atts, $content, $shortcode_tag ) {
			return self::get_font_samples('shortcode');
		}); 

		// Add custom fonts to Bricks Builder UI.
		add_filter( 'bricks/builder/standard_fonts', function($fonts) {
			return array_merge($fonts, self::get_font_families());
		});
	}
	//-------------------------------------------------------------------------------------------------------------------
	static function init_admin_menu() {
		$st = microtime(true);
		// Add submenu page to the Appearance menu.
		add_action('admin_menu', function(){
			add_submenu_page(	'themes.php', 										// parent slug of "Appearance"
								//'ct_dashboard_page',								// parent slug of "Oxygen"
								_x(self::TITLE,'page title','ma_customfonts'), 		// page title
								_x(self::TITLE,'menu title','ma_customfonts'), 		// menu title
								'manage_options',									// capabilitiy
								'ma_customfonts',									// menu slug
								[__CLASS__, 'admin_customfonts']					// function
							);
		});
		$et = microtime(true);
		if (WP_DEBUG && self::$timing) {error_log(sprintf('%s::%s() Timing: %.5f sec.',__CLASS__,__FUNCTION__,$et-$st));}
	}
	//-------------------------------------------------------------------------------------------------------------------
	// Proper quoting for font families: Detect multiple families and quote individually if required
	static function quote_font_names($font_spec) {
		$fonts = preg_split('/,\s*/', $font_spec, -1, PREG_SPLIT_NO_EMPTY);
		$fonts = array_map(function($name){
			return preg_match('/[^A-Za-z\-]/',$name) ? '"'.$name.'"' : $name;
		},$fonts);
		$retval = implode(', ',$fonts);
		return $retval;
	}

	//-------------------------------------------------------------------------------------------------------------------
	static function get_script_version() {
		$implementation = basename(__FILE__) == 'ma-oxygen-custom-fonts.php' ? 'Plugin' : 'Code Snippet';
		return sprintf('%s, %s', $implementation, self::VERSION);
	}
	//-------------------------------------------------------------------------------------------------------------------
	// Admin function Appearance > Custom Fonts to display samples of all detected fonts
	static function admin_customfonts() {
		$output =	'<div class="wrap">'.
						'<h1>' . esc_html(get_admin_page_title()) . '</h1>'.
						self::get_font_samples('admin').
					'</div>';
		echo $output;
		echo self::get_font_css();
	}
	//-------------------------------------------------------------------------------------------------------------------
	// parses weight from a font file name (not used for Web Font Loader packages)
	static function parse_font_name($name) {
		// already in cache?
		if (array_key_exists($name,self::$fonts_details_cache)) {return self::$fonts_details_cache[$name];}
		
		$retval = (object)['name'=>$name, 'weight'=>400, 'style'=>'normal'];
		if (!self::$parsename) {return $retval;}
		$st = microtime(true);
		$weights = (object)[ // must match from more to less specific !!
			// more specific
			200 => '/[ \-]?(200|((extra|ultra)\-?light))/i',
			800 => '/[ \-]?(800|((extra|ultra)\-?bold))/i',
			600 => '/[ \-]?(600|([ds]emi(\-?bold)?))/i',
			// less specific
			100 => '/[ \-]?(100|thin)/i',
			300 => '/[ \-]?(300|light)/i',
			400 => '/[ \-]?(400|normal|regular|book)/i',
			500 => '/[ \-]?(500|medium)/i',
			700 => '/[ \-]?(700|bold)/i',
			900 => '/[ \-]?(900|black|heavy)/i',
			'var' => '/[ \-]?(VariableFont|\[wght\])/i',
		];
		$count = 0;
		// detect & cut style
		$new_name = preg_replace('/[ \-]?(italic|oblique)/i', '', $retval->name, -1, $count); 
		if ($new_name && $count) {
			$retval->name = $new_name;
			$retval->style = 'italic';
		}
		// detect & cut weight
		foreach ($weights as $weight => $pattern) {
			$new_name = preg_replace($pattern, '', $retval->name, -1, $count);
			if ($new_name && $count) {
				$retval->name = $new_name;
				$retval->weight = $weight;
				break;
			}
		}
		// cut -webfont
		$retval->name = preg_replace('/[ \-]?webfont$/i', '', $retval->name); 
		// variable font: detect & cut specifica
		if ($retval->weight == 'var') {
			$retval->name = preg_replace('/_(opsz,wght|opsz|wght)$/i', '', $retval->name); 
		}
		// store to cache
		self::$fonts_details_cache[$name] = $retval;
		$et = microtime(true);
		if (WP_DEBUG && self::$timing) {error_log(sprintf(' %s::%s() Timing: %.5f sec.',__CLASS__,__FUNCTION__,$et-$st));}
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	// construct CSS block from CSS properties stored in JSON from Web Font Loader
	static 	function create_css_from_ruleset($css_ruleset) {
		$retval = '';
		if (isset($css_ruleset)) {
			if (isset($css_ruleset->{'comment'})) {$retval .= sprintf("/* %s */\n",$css_ruleset->{'comment'});}
			$retval .= "@font-face {\n";
			$retval .= sprintf("\tfont-family: '%s';\n",$css_ruleset->{'font-family'});
			$retval .= sprintf("\tfont-style: %s;\n",$css_ruleset->{'font-style'});
			$retval .= sprintf("\tfont-weight: %s;\n",$css_ruleset->{'font-weight'});
			$retval .= sprintf("\tsrc: url('%s') format('%s');\n",$css_ruleset->{'url'}, $css_ruleset->{'format'});
			if (isset($css_ruleset->{'unicode-range'})) {$retval .= sprintf("\tunicode-range: %s;\n", $css_ruleset->{'unicode-range'});}
			if (self::$fontdisplay) {
				$retval .= sprintf("\tfont-display: %s;\n",self::$fontdisplay);
			}			
			$retval .= '}';
		}
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	// return base dir/url for fonts. Create directory if necessary
	private static function get_fonts_base() {
		$retval = (object)['dir'=>null,'url'=>''];
		$fonts_dir_info = wp_get_upload_dir();
		$retval->dir = $fonts_dir_info['basedir'].'/fonts';
		$retval->url = $fonts_dir_info['baseurl'].'/fonts';
		// create fonts folder if not exists
		if (!file_exists($retval->dir)) {
			if (!@mkdir($retval->dir)) {
				add_action('admin_notices', function(){
					echo '<div class="notice notice-error"><p>['.self::TITLE.'] Error creating fonts base folder <code>wp-content/uploads/fonts</code>.</p></div>';
				});
				error_log(sprintf('%s::%s() Error creating fonts base folder.', __CLASS__, __FUNCTION__)); 
				return null;
			}
		}
		if (!is_writable($retval->dir)) {
			add_action('admin_notices', function(){
				echo '<div class="notice notice-error"><p>['.self::TITLE.'] Folder <code>wp-content/uploads/fonts</code> is not writable. Please correct folder permissions.</p></div>';
			});
		}
		// V3.2.2, create scheme-less URL
		$retval->url = preg_replace('/^https?\:/','',$retval->url);

		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	// find font files in font folder
	static function find_fonts() {
		$st = microtime(true);
		if (isset(self::$fonts)) return;
		self::$fonts = [];
		// property $recursive either recursive or flat file scan
		if (self::$recursive) {
			// recursive scan for font files (including subdirectories)
			$directory_iterator = new RecursiveDirectoryIterator(self::$fonts_base->dir,  RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS);
			$file_iterator = new RecursiveIteratorIterator($directory_iterator);
		} else {
			// flat scan for font files (no subdirectories)
			$file_iterator = new FilesystemIterator(self::$fonts_base->dir);
		}
		// loop through files and collect font and JSON files
		$font_splfiles = [];
		$json_splfiles = [];
		foreach( $file_iterator as $file) {
			// V3: A JSON file might be available from Web Font Loader
			if ($file->getExtension() == 'json') {
				$json_splfiles[] = $file;
			}
			if (in_array(strtolower($file->getExtension()), self::$prioritized_formats)) {
				$font_splfiles[] = $file;
			}
		}
		
		// V3: check JSON files. If it defines "family" read the font name and CSS
		$json_font_families = [];
		foreach ($json_splfiles as $json_splfile) {
			if ($font_details = @json_decode(@file_get_contents($json_splfile->getPathname()))) {
				// It's a JSON from Web Font Loader?
				if (isset($font_details->creator) && (strpos($font_details->creator, 'Web Font Loader')=== 0)) {
					// store font family name 
					$json_font_families[$json_splfile->getBasename('.json')] = $font_details->family;
					// drop all collected font files for that font since they are listed in JSON file
					$font_path = $json_splfile->getPath().'/';
					foreach ($font_splfiles as $idx => $font_splfile) {
						if (strpos($font_splfile->getPath().'/',$font_path) === 0) {
							self::$font_files_cnt ++;
							unset($font_splfiles[$idx]);
						}
					}
					// get the relative path
					$font_path_relative = str_replace(self::$fonts_base->dir,'',$font_path);
					// encode every single path element since we might have spaces or special chars 
					$font_path_url = implode('/',array_map('rawurlencode',explode('/',$font_path_relative)));
					
					// add CSS blocks (could be multiple unicode ranges) to fonts list
					$font_baseurl = self::$fonts_base->url . $font_path_url;
					foreach ($font_details->css as $css_ruleset) {
						// check for WOFF support. Skip if not enabled
						if ($css_ruleset->format=='woff' && !self::$wfl_support_woff) {continue;}
						// check if file exists. User might have deleted it.
						if (!file_exists($font_path . $css_ruleset->url)) {continue;}
						self::$fonts[$css_ruleset->{'font-family'}]['source'] = 'Web Font Loader';
						self::$fonts[$css_ruleset->{'font-family'}][$css_ruleset->{'font-weight'}.'/'.$css_ruleset->{'font-style'}]['has_css'] = true;
						// only formats woff and woff2, so just use format as file extension slot
						if (!isset(self::$fonts[$css_ruleset->{'font-family'}][$css_ruleset->{'font-weight'}.'/'.$css_ruleset->{'font-style'}][$css_ruleset->{'format'}])) {
							self::$fonts[$css_ruleset->{'font-family'}][$css_ruleset->{'font-weight'}.'/'.$css_ruleset->{'font-style'}][$css_ruleset->{'format'}] = [];	
						}
						$css_ruleset->url = $font_baseurl . $css_ruleset->url;

						$css_block = self::create_css_from_ruleset($css_ruleset);
						self::$fonts[$css_ruleset->{'font-family'}][$css_ruleset->{'font-weight'}.'/'.$css_ruleset->{'font-style'}][$css_ruleset->{'format'}][] = $css_block;
					}
				}
			}
		}
		// collect font definitions
		foreach ($font_splfiles as $font_splfile) {
			self::$font_files_cnt ++;
			$font_ext = $font_splfile->getExtension();
			$font_details = self::parse_font_name($font_splfile->getbasename('.'.$font_ext));
			$font_name = $font_details->name;
			if (in_array($font_name,array_values($json_font_families))) {
				// already found this font from Web Font Loader. Skip.
				continue;
			}
			$font_weight = $font_details->weight;
			$font_style = $font_details->style;
			$font_path = str_replace(self::$fonts_base->dir,'',$font_splfile->getPath());
			// encode every single path element since we might have spaces or special chars 
			$font_path = implode('/',array_map('rawurlencode',explode('/',$font_path)));
			// create entry for this font name
			if (!array_key_exists($font_name,self::$fonts)) {self::$fonts[$font_name] = [];}
			// create entry for this font weight/style 
			if (!array_key_exists($font_weight.'/'.$font_style,self::$fonts[$font_name])) {self::$fonts[$font_name][$font_weight.'/'.$font_style] = [];}
			// store font details for this file
			self::$fonts[$font_name][$font_weight.'/'.$font_style][strtolower($font_ext)] = self::$fonts_base->url . $font_path . '/' . rawurlencode($font_splfile->getBasename());
		}
		ksort(self::$fonts, SORT_NATURAL | SORT_FLAG_CASE);
		$et = microtime(true);
		if (WP_DEBUG && self::$timing) {error_log(sprintf('%s::%s() %d font files, %d font families.',__CLASS__,__FUNCTION__, self::$font_files_cnt, count(self::$fonts)));}
		if (WP_DEBUG && self::$timing) {error_log(sprintf('%s::%s() Timing: %.5f sec.',__CLASS__,__FUNCTION__,$et-$st));}
	}
	//-------------------------------------------------------------------------------------------------------------------
	// returns a list of font families
	static function get_font_families() {
		// Oxygen 4.0.4  Pro Menu calls ECF_Plugin::get_font_families() as Ajax call. 
		// Our class doesn't get initialized for Ajax calls and doesn't have $fonts_base set.
		// Just return an empty fonts list. 
		if (!isset(self::$fonts_base)) {return [];}

		if (!isset(self::$fonts)) self::find_fonts();
		$st = microtime(true);
		$font_family_list = [];
		foreach (array_keys(self::$fonts) as $font_name) {
			$font_family_list[] = $font_name;
		}
		$et = microtime(true);
		if (WP_DEBUG && self::$timing) {error_log(sprintf('%s::%s() Timing: %.5f sec.',__CLASS__,__FUNCTION__,$et-$st));}
		return $font_family_list;
	}
	//-------------------------------------------------------------------------------------------------------------------
	// we call this function from footer emitter to get font definitions for emitting required files
	static function get_font_definitions() {
		return self::$fonts;
	}
	//-------------------------------------------------------------------------------------------------------------------
	// creates CSS for custom fonts. 
	// For $cssoutput 'file', return <link rel="stylesheet" ...>, for 'html' <style>...</style>
	// If $plaincss is true, return plain css instead
	static function get_font_css($plaincss = false) {
		// emit CSS for fonts in footer
		$version = self::get_script_version();
		$style = '';
		$st = microtime(true);
		foreach (self::$fonts as $font_name => $font_details) {
			ksort($font_details);
			foreach ($font_details as $weight_style => $file_list) {
				if ($weight_style=='source') {continue;}
				list ($font_weight,$font_style) = explode('/',$weight_style);

				if (isset($file_list['has_css'])) {
					// V3: Google Font package CSS from Web Font Loader already has CSS
					foreach (array_reverse(self::$prioritized_formats) as $font_ext) {
						// we only have woff and woff2
						if (!isset($file_list[$font_ext])) { continue; }
						foreach ($file_list[$font_ext] as $css) {
							$style .= trim($css).PHP_EOL;
						}
					}
				} else {
					// V2: Only have font info and file names. Build CSS
					if ($font_weight == 'var') {
						$font_weight_output = '1 1000';
					} else {
						$font_weight_output = $font_weight;
					}
					$style .= 	'@font-face{'.PHP_EOL.
								'  font-family:"'.$font_name.'";'.PHP_EOL.
								'  font-weight:'.$font_weight_output.';'.PHP_EOL.
								'  font-style:'.$font_style.';'.PHP_EOL;
								// .eot needs special handling for IE9 Compat Mode
					if (array_key_exists('eot',$file_list)) {$style .= '  src:url("'.$file_list['eot'].'");'.PHP_EOL;}
					$urls = [];

					// output font sources in prioritized order
					foreach (self::$prioritized_formats as $font_ext) {
						if (array_key_exists($font_ext,$file_list)) {
							$font_url = $file_list[$font_ext];
							$format = '';
							switch ($font_ext) {
								case 'eot': $format = 'embedded-opentype'; break;
								case 'otf': $format = 'opentype'; break;
								case 'ttf': $format = 'truetype'; break;
								// others have same format as extension (svg, woff, woff2)
								default:	$format = strtolower($font_ext);
							}
							if ($font_ext == 'eot') {
								// IE6-IE8
								$urls[] = 'url("'.$font_url.'?#iefix") format("'.$format.'")';
							} else {
								$urls[] = 'url("'.$font_url.'") format("'.$format.'")';
							}
						}
					}
					$style .= '  src:' . join(','.PHP_EOL.'      ',$urls) . ';'.PHP_EOL;
					if (self::$fontdisplay) {
						$style .= sprintf('  font-display: %s;'.PHP_EOL,self::$fontdisplay);
					}
					$style .= '}'.PHP_EOL;
				}
			}		
			
		}
		// if Oxygen Builder is active, emit CSS to show custom fonts in light blue.
		$builder_style = defined('SHOW_CT_BUILDER') ? 'div.oxygen-select-box-option.ng-binding.ng-scope[ng-repeat*="elegantCustomFonts"] {color:lightblue !important;}' : '';
		
		// minimize string if configured
		if (self::$cssminimize) {
			$style = preg_replace('/\r?\n */','',$style); 
		}

		$retval = '';
		if (self::$cssoutput == 'file') {
			// option: write CSS to file
			$css_path = self::$fonts_base->dir.'/ma_customfonts.css';
			$css_code = '/* Version: '.$version.' */'.PHP_EOL.$style;
			$css_hash_code = hash('CRC32', $css_code, false);
			$css_hash_file = file_exists($css_path) ? hash_file('CRC32', $css_path, false) : 0;
			if ($css_hash_code !== $css_hash_file) {
				if (WP_DEBUG && self::$timing) {error_log(sprintf('%s::%s() Writing CSS file "%s"',__CLASS__,__FUNCTION__,$css_path));}
				$status = file_put_contents($css_path, $css_code);
				if ($status === false) {error_log(sprintf('%s::%s() Error writing CSS file "%s"',__CLASS__,__FUNCTION__,$css_path));}
				$css_hash_file = file_exists($css_path) ? hash_file('CRC32', $css_path, false) : 0;
			}
			$css_url = str_replace(self::$fonts_base->dir,self::$fonts_base->url ,$css_path);
			$retval = sprintf('<link id="ma-customfonts" href="%s?ver=%s" rel="stylesheet" type="text/css" />%s',$css_url, $css_hash_file, $builder_style?'<style>'.$builder_style.'</style>':'');
		}
		if (self::$cssoutput == 'html') {
			// option: write CSS to html
			$retval = '<style id="ma-customfonts">'.'/* Version: '.$version.' */'.PHP_EOL.$style.PHP_EOL.$builder_style.'</style>';
		}
		if ($plaincss) {
			$retval = $style.PHP_EOL.$builder_style;
		}

		$et = microtime(true);
		if (WP_DEBUG && self::$timing) {error_log(sprintf('%s::%s() Timing: %.5f sec.',__CLASS__,__FUNCTION__,$et-$st));}
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	// parses font file url from CSS block
	static function get_font_file_info_from_css($css) {
		$retval = [];
		if (!is_array($css)) {$css = [$css];}
		foreach($css as $css_block) {
			if (preg_match('/url\(\'(.*?)\'\)/',$css_block,$matches)) {
				$retval[] = $matches[1];
			}
		}
		$retval = array_unique($retval); 
		return $retval;
	}
	
	//-------------------------------------------------------------------------------------------------------------------
	// get font file urls for var weight
	static function get_font_var_weight_urls($font_details) {
		$retval = [];
		if (isset($font_details) && (is_array($font_details))) {
			// check if we have a 'var' weight array key
			foreach ($font_details as $weight_style => $details) {
				// check key (for custom fonts)
				if (strpos($weight_style,'var') === 0) {
					foreach (self::$var_weight_formats as $format) { // support var weight for specific formats
						if (isset($details[$format])) {
							$retval[] = urldecode($details[$format]);
						}
					}
					goto DONE;
				}
				// check CSS file names of woff2 files (for WebFontLoader fonts)
				if (isset($details['woff2']) && is_array($details['woff2'])) {
					foreach ($details['woff2'] as $css_rule) {
						if (preg_match('/src: url\(\'(.*?)\'\)/',$css_rule,$matches)) {
							$src = $matches[1];
							if (preg_match('/[ \-]?(VariableFont|\[wght\])/i',$src)) {
								$retval[] = $src;
							}
						}
					}
				}
			}
		}
		DONE:
		// remove duplicates
		$retval = array_unique($retval);
		// remove base url
		foreach ($retval as &$url) {
			$url = str_replace(self::$fonts_base->url.'/','',$url);
		}
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	// returns font url (string or array) shortened and urldecoded 
	static function get_font_short_display_url ($urls) {
		$retval = [];
		if ($urls) {
			if (!is_array($urls)) {$urls = [$urls];}
			foreach ($urls as $url) {
				// cut leading path/url from file info
				$url = str_replace(self::$fonts_base->url.'/','',$url);
				// decode html entities (e.g. %20) in file path
				$url = implode('/',array_map('rawurldecode',explode('/',$url)));
				$retval[] = $url;
			}
		}
		return implode(', ',$retval);
	}
	//-------------------------------------------------------------------------------------------------------------------
	// returns HTML code to display all registered custom fonts
	// $mode 'admin':		formatting to be displayed on WP Admin > Appearance
	// $mode 'shortcode':	formatting to be displayed as shortcode output
	static function get_font_samples($mode = null) {
		$st = microtime(true);
		$script_version = self::get_script_version();
		$sample_text = self::$sample_text;
		$output_style = <<<'END_OF_STYLE'
		<style>
		.ma-customfonts-test {font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif;font-size:13px;}
		.ma-customfonts-header {border:1px solid darkgray;border-radius:10px;padding:10px;padding-top:0;}
		.ma-customfonts-header > div {display:flex;flex-direction:row;margin-top:.5em;}
		.ma-customfonts-label {flex-shrink:0;display:inline-block;width:110px;}

		#ma-customfonts-input-font-size {width:60px;text-align:center;min-height:1.5em;line-height:1em;padding:0;}
		#ma-customfonts-input-sample-text {width:100%;max-width:400px;text-align:left;}

		.ma-customfonts-legend {margin-top:3em;border:1px solid darkgray;border-radius:10px;padding:10px;padding-top:0;}
		.ma-customfonts-legend > div {display:flex;flex-direction:row;margin-top:.5em;}
		.ma-customfonts-legend > div > span:first-child {width:110px;flex-shrink:0;}

		.ma-customfonts-timing {margin-top:2em;font-size:.8em;}
		.ma-customfonts-timing table {border-spacing:0};
		.ma-customfonts-timing td {padding:0}

		.ma-customfonts-test h3 {margin-top:20px;padding-bottom:0;}
		.ma-customfonts-font-additional-info {margin-left:1em;margin-bottom:1em;font-size:13px;font-weight:normal;}

		.ma-customfonts-font-row {display:flex;flex-direction:row;justify-content:space-between;align-items:center;padding:0;line-height:20px;border-bottom:1px solid #e0e0e0;margin:0 1em;}
		.ma-customfonts-font-row:hover {background-color:lightgray;}
		.ma-customfonts-font-info {font-size:10px;line-height:1em;width:100px;}
		.ma-customfonts-font-sample {font-size:16px;line-height:1.2em;flex-grow:1;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;}
		.ma-customfonts-format-info {font-size:10px;cursor:help;margin-left:1em;}
		.ma-customfonts-format-info.simulated {color:gray;font-style:italic;cursor:alias;} 
		.ma-customfonts-format-info :is(.eot,.ttf,.svg) {color:red;}
		.ma-customfonts-format-info :is(.ttf,.otf) {color:chocolate;}
		.ma-customfonts-format-info :is(.woff) {color:orange;}
		.ma-customfonts-format-info :is(.woff2) {color:green;}

		.ma-customfonts-simulated {display:none;}
		@media (max-width:700px) {
			.ma-customfonts-font-row {flex-wrap:wrap;}
			.ma-customfonts-font-info {order:1;}
			.ma-customfonts-font-sample {order:3; width:100%;}
			.ma-customfonts-format-info {order:2;}
		}
		</style>
END_OF_STYLE;
		$var = (object)[
			'container_style'	=> $mode=='shortcode' ? 'style="border:1px dashed darkgray;padding:10px;"' : '',
			'title'				=> $mode=='shortcode' ? '<h2>'.self::TITLE.'</h2>' : '',
			'cnt_families'		=> count(self::$fonts),
			'cnt_files'			=> self::$font_files_cnt,
		];
		$output_header = <<<END_OF_HEADER
		<div class="ma-customfonts-test" {$var->container_style}>
			{$var->title}
			<div class="ma-customfonts-header">
				<div>
					<span class="ma-customfonts-label">Version:</span>
					<span>{$script_version}<span>
				</div>
				<div>
					<span class="ma-customfonts-label">Font Families:</span>
					<span>{$var->cnt_families}<span>
				</div>
				<div>
					<span class="ma-customfonts-label">Font Files:</span>
					<span>{$var->cnt_files}</span>
				</div>
				<div>
					<span class="ma-customfonts-label">Sample Font Size:</span>
					<span><input id="ma-customfonts-input-font-size" type="number" value="16" onchange="ma_customfonts_change_font_size();"> px</span>
				</div>
				<div>
					<span class="ma-customfonts-label">Sample Text:</span>
					<input id="ma-customfonts-input-sample-text" value="{$sample_text}" onkeyup="ma_customfonts_change_sample_text();">
				</div>
				<div>
					<span class="ma-customfonts-label">Simulated:</span>
					<span><input id="ma-customfonts-input-simulated" type="checkbox" value="simulated" onchange="ma_customfonts_toggle_simulated();"> Show font weights/styles without files as browser would simulate.</span>
				</div>
			</div>
END_OF_HEADER;
		$output_legend = <<<END_OF_LEGEND
		<fieldset class="ma-customfonts-legend">
			<legend><strong>Font File Formats</strong></legend>
			<div><span style="color:green">WOFF2</span><span>Modern formats are perfect for web.</span></div>
			<div><span style="color:orange">WOFF</span><span>Older formats can be used for web but have larger file sizes.</span></div>
			<div><span style="color:chocolate">OTF, TTF</span><span>Formats meant for desktop use, can be used for web but have larger to huge file sizes.</span></div>
			<div><span style="color:red">EOT, SVG</span><span>Some formats are only supported on specific browsers like EOT on IE, SVG on Safari. Deprecated!</span></div>
		</fieldset>

END_OF_LEGEND;
		// controls
		$output_script = <<<'END_OF_SCRIPT'
		<script>
		function changeCss($className, $classValue) {
			// we need invisible container to store additional css definitions
			let $cssMainContainer = document.querySelector('#ma-customfonts-css-modifier-container');
			if ($cssMainContainer === null) {
				$cssMainContainer = document.createElement('div');
				$cssMainContainer.id = 'ma-customfonts-css-modifier-container';
				$cssMainContainer.style.display = 'none';
				document.body.appendChild($cssMainContainer);
			}
			// we need one div for each class
			let $classContainer = $cssMainContainer.querySelector('div[data-class="' + $className + '"]');
			if ($classContainer === null) {
				$classContainer = document.createElement('div');
				$classContainer.dataset.class = $className;
				$cssMainContainer.appendChild($classContainer);
			}
			// set class style
			$classContainer.innerHTML = '<style type="text/css">.'+$className+' {'+$classValue+'}</style>';
		}
		function ma_customfonts_change_font_size() {
			let $val = document.querySelector('#ma-customfonts-input-font-size').value;
			changeCss('ma-customfonts-font-sample','font-size: '+$val+'px;');
		}
		function ma_customfonts_change_sample_text() {
			let $val = document.querySelector('#ma-customfonts-input-sample-text').value;
			document.querySelectorAll('.ma-customfonts-font-sample').forEach( ($elm) => {$elm.textContent=$val;} );
		}
		function ma_customfonts_toggle_simulated() {
			let $simulated = document.querySelector('#ma-customfonts-input-simulated').checked;
			document.querySelectorAll('.ma-customfonts-simulated').forEach( ($elm) => {$elm.style.display = $simulated?'flex':'none';} );
		}
		</script>
END_OF_SCRIPT;
		

		// prepare tags for every weight/style combination
		$weights = [100,200,300,400,500,600,700,800,900];
		$styles = ['normal','italic'];
		$weights_styles = [];
		foreach ($weights as $weight) { foreach ($styles as $style) { $weights_styles[] = $weight.'/'.$style; } }
		// prepare data structure to display fonts in each weight/style combination
		$samples = [];
		foreach (array_keys(self::$fonts) as $font_name) {
			$samples[$font_name] = [
				'has_var_weight' => false,
				'weights_styles' => [],
			];
			foreach ($weights_styles as $weight_style) {
				$samples[$font_name]['weights_styles'][$weight_style] = [];
			}
		}
		// collect available font files
		foreach (self::$fonts as $font_name => $font_details) {
			ksort($font_details);

			// check if we have a 'var' weight
			$font_var_weight_urls = self::get_font_var_weight_urls($font_details);
			if ($font_var_weight_urls) {$samples[$font_name]['has_var_weight'] = true;}
			$samples[$font_name]['source'] = $font_details['source'] ?? null;

			// loop font details and fill available samples and formats
			foreach ($font_details as $weight_style => $formats) {
				if ($weight_style=='source') {continue;}
				list ($weight,$style) = explode('/',$weight_style);
				
				if ($weight == 'var') {
					//  var weight detected from file name; we don't know the supported weights; fill all weights for current style
					foreach (self::$var_weight_formats as $format) { // support var weight for specific formats
						if (isset($formats[$format])) {
							$url = $formats[$format];
							$url = self::get_font_short_display_url($url);
							foreach ($weights as $var_weight) {
								$samples[$font_name]['weights_styles'][$var_weight.'/'.$style][$format] = $url;
							}
						}
					}
				} 
				// fill non var weight formats 
				foreach ($formats as $format => $url) {
					if (!in_array($format,self::$prioritized_formats)) {continue;} // skip 'has_css'
					if ($weight != 'var') {
						if (is_array($url)) { // seems to be an array of CSS rules
							$url = self::get_font_file_info_from_css($url);
						}
						$url = self::get_font_short_display_url($url);
						$samples[$font_name]['weights_styles'][$weight.'/'.$style][$format] = $url;
					}
				}
			}
		}

		$output_sample = '';
		foreach ($samples as $font_name => $font_details) {
			
			// output the font sample block
			$output_sample .= sprintf('<h3>%1$s</h3>',$font_name);
			// additional font info (variable fonts, source Web Font Loader, WOFF support)
			$font_additional_infos = [];
			if ($font_details['has_var_weight']) {$font_additional_infos[] = 'Variable Weight Font.';}
			if ($font_details['source'] == 'Web Font Loader') {
				$font_additional_infos[] = 'Downloaded from <a href="https://webfontloader.altmann.de/" target="_blank">Web Font Loader</a>.';
				if (!self::$wfl_support_woff) {$font_additional_infos[] = 'WOFF files are ignored according to configuration setting.';}
			}
			$output_sample .= count($font_additional_infos) ? '<div class="ma-customfonts-font-additional-info">'.implode(' ',$font_additional_infos).'</div>' : '';

			foreach ($font_details['weights_styles'] as $weight_style => $formats) {
				if ($weight_style=='source') {continue;}
				list ($weight,$style) = explode('/',$weight_style);
				// build font file info output
				$font_file_list = [];
				foreach ($formats as $format => $files) {
					if (is_array($files)) {$files = implode("\n",$files);}
					$font_file_list[$format] = sprintf('<span class="%3$s" title="%2$s">%1$s</span>', strtoupper($format), $files, $format);
				}
				$font_file_info = '<span class="ma-customfonts-format-info">(' . implode(', ',array_values($font_file_list)) . ')</span>';
				$output_sample .= sprintf(	'<div class="ma-customfonts-font-row '.($font_file_list?'':'ma-customfonts-simulated').'">'.
												'<span class="ma-customfonts-font-info">%2$s %3$s</span>'.
												'<span class="ma-customfonts-font-sample" style="font-family:\'%1$s\';font-weight:%2$d;font-style:%3$s">%4$s</span>'.
												'%5$s'.
											'</div>',$font_name, $weight, $style, $sample_text, $font_file_list?$font_file_info:'<span class="ma-customfonts-format-info simulated">(simulated)</span>');
			}
		}
		// output timing
		$timing = (object)[
			'fonts_collect'	=> sprintf('%.4f',self::$timing_fonts_collect),
			'fonts_css'		=> sprintf('%.4f',self::$timing_fonts_css),
			'output'		=> sprintf('%.4f',microtime(true)-$st),
		];
		$output_timing = <<<END_OF_TIMING
		<div class="ma-customfonts-timing">
			<strong>Timing</strong>
			<table>
				<tr><td>Collecting fonts:</td><td>{$timing->fonts_collect} seconds</td></tr>
				<tr><td>Generating CSS:</td><td>{$timing->fonts_css} seconds</td></tr>
				<tr><td>Generating output:</td><td>{$timing->output} seconds</td></tr>
			</table>
		</div>
END_OF_TIMING;

		$output =  $output_style . $output_header . $output_script . $output_sample . $output_legend . $output_timing.'</div>';

		$et = microtime(true);
		if (WP_DEBUG && self::$timing) {error_log(sprintf('%s::%s() Timing: %.5f sec.',__CLASS__,__FUNCTION__,$et-$st));}
		return $output;

	}
} // end of class MA_CustomFonts


endif; // end of conditional implementations


//===================================================================================================================
// Warn about incompatibilities
add_action('wp_loaded',function(){
	$GLOBALS['ma_customfonts_incompatibilities'] = [];
	if (is_admin()) {
		// Plugin "Elegant Custom Fonts"
		if (function_exists('is_plugin_active') && is_plugin_active('elegant-custom-fonts/elegant-custom-fonts.php'))
			{$GLOBALS['ma_customfonts_incompatibilities'][] = '"Elegant Custom Fonts"';}
		// Plugin "Use Any Font"
		if (function_exists('is_plugin_active') && is_plugin_active('use-any-font/use-any-font.php'))
			{$GLOBALS['ma_customfonts_incompatibilities'][] = '"Use Any Font"';}
		// Plugin "Swiss Knife" with feature "Font Manager" active
		if (function_exists('is_plugin_active') && is_plugin_active('swiss-knife/swiss-knife.php') && (get_option('swiss_font_manager')=='yes'))
			{$GLOBALS['ma_customfonts_incompatibilities'][] = '"Swiss Knife" with feature "Font Manager" enabled';}
		if (count($GLOBALS['ma_customfonts_incompatibilities'])) {
			add_action('admin_notices', function(){
				if (WP_DEBUG ) {error_log('MA_CustomFonts / Incompatibilities: '.print_r($GLOBALS['ma_customfonts_incompatibilities'],true));}
				echo '<div class="notice notice-warning is-dismissible">
						<p>The Code Snippet "Oxygen: Custom Fonts" is not compatible with the Plugin '.implode(' or ',$GLOBALS['ma_customfonts_incompatibilities']).'.<br/>
						Please deactivate either the Code Snippet or the Plugin (feature).</p>
					</div>';
			});
		}
	}
	if (count($GLOBALS['ma_customfonts_incompatibilities'])) return;
	

	//-------------------------------------------------------------------------------------------------------------------
	// create a primitive ECF_Plugin class if plugin "Elegant Custom Fonts" is not installed
	if (!count($GLOBALS['ma_customfonts_incompatibilities']) && !class_exists('ECF_Plugin')) {
		class ECF_Plugin {
			static function get_font_families() {
				$st = microtime(true);
				$font_family_list = MA_CustomFonts::get_font_families();
				$et = microtime(true);
				if (WP_DEBUG && MA_CustomFonts::$timing) {error_log(sprintf('MA_CustomFonts/%s::%s() Timing: %.5f sec.',__CLASS__,__FUNCTION__,$et-$st));}
				return $font_family_list;
			}
		}
		global $ECF_Plugin;
		$ECF_Plugin = new ECF_Plugin();
	}
	

},1000); // hook late to check other plugins!


//===================================================================================================================
// Initialize
add_action('wp_loaded',function(){
	if (count($GLOBALS['ma_customfonts_incompatibilities'])) return;
	if (wp_doing_ajax()) 		return;	// don't run for AJAX requests
	if (wp_doing_cron()) 		return;	// don't run for CRON requests
	if (wp_is_json_request()) 	return;	// don't run for JSON requests
	if (is_favicon()) 			return;	// don't run for favicon request
	if (isset($_SERVER['QUERY_STRING']) && ($_SERVER['QUERY_STRING'] == 'service-worker'))	return;	// don't run for service-worker
	if (isset($_SERVER['REQUEST_URI']) 	&& ($_SERVER['REQUEST_URI'] == '/favicon.ico'))		return;	// don't run for favicon

	if (WP_DEBUG && MA_CustomFonts::$timing) {error_log(sprintf('MA_CustomFonts: Request URI="%s" action="%s"', $_SERVER['REQUEST_URI']??'', $_REQUEST['action']??''));}

	if (is_admin()) {
		// init custom font functionality only when needed
		global $pagenow;
		if ( 	(($_REQUEST['page']??'') === 'ma_customfonts') 										// custom font test 
			||	($pagenow === 'post-new.php')														// new post
			||	($pagenow === 'post.php' && ($_REQUEST['action']??'') === 'edit')					// edit post
		) {
			MA_CustomFonts::init();
		} else {
			if (WP_DEBUG && MA_CustomFonts::$timing) {error_log(sprintf('MA_CustomFonts: Skipping font initialization for this request.'));}
		}
		// initialize admin menu Appearance > Custom Fonts
		MA_CustomFonts::init_admin_menu();
	} else {
		// frontend
		MA_CustomFonts::init();
	}
},1001); // hook after incompatibility check
