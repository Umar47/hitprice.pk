<?php
/**
 * Mock AI response generator.
 *
 * Returns realistic sample content per content type without
 * making any API calls. Used when hp_ai_mode is 'mock'.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HP_AI_Mock {

	/**
	 * Generate a mock social post response.
	 *
	 * @param object $topic Topic row object.
	 * @return array Structured social post data.
	 */
	public static function social( $topic ) {

		$pools = self::get_social_pools();
		$type  = $topic->content_type;

		if ( ! isset( $pools[ $type ] ) ) {
			$type = 'comparison';
		}

		$variants = $pools[ $type ];
		$pick     = $variants[ array_rand( $variants ) ];

		// Inject topic title into templates.
		$title = $topic->title;

		return array(
			'caption'        => str_replace( '{title}', $title, $pick['caption'] ),
			'hook_line'      => str_replace( '{title}', $title, $pick['hook_line'] ),
			'hashtags'       => $pick['hashtags'],
			'image_text'     => str_replace( '{title}', $title, $pick['image_text'] ),
			'carousel_ideas' => wp_json_encode( $pick['carousel_ideas'] ),
			'cta_text'       => $pick['cta_text'],
			'platform'       => 'facebook',
			'ai_provider'    => 'mock',
			'ai_model'       => 'mock-v1',
			'tokens_used'    => 0,
		);
	}

	/**
	 * Generate a mock blog post response.
	 *
	 * @param object $topic Topic row object.
	 * @return array Structured blog post data.
	 */
	public static function blog( $topic ) {

		$pools = self::get_blog_pools();
		$type  = $topic->content_type;

		if ( ! isset( $pools[ $type ] ) ) {
			$type = 'comparison';
		}

		$variants = $pools[ $type ];
		$pick     = $variants[ array_rand( $variants ) ];

		$title    = $topic->title;
		$keywords = $topic->keywords ? $topic->keywords : 'mobile phones pakistan';
		$keyword_arr = array_map( 'trim', explode( ',', $keywords ) );
		$focus    = ! empty( $keyword_arr[0] ) ? $keyword_arr[0] : 'mobile phones';

		return array(
			'title'                 => str_replace( '{title}', $title, $pick['title'] ),
			'slug'                  => sanitize_title( str_replace( '{title}', $title, $pick['title'] ) ),
			'content'               => str_replace( '{title}', $title, $pick['content'] ),
			'excerpt'               => str_replace( '{title}', $title, $pick['excerpt'] ),
			'meta_title'            => str_replace( '{title}', $title, $pick['meta_title'] ),
			'meta_description'      => str_replace( '{title}', $title, $pick['meta_description'] ),
			'focus_keyword'         => sanitize_text_field( $focus ),
			'featured_image_prompt' => str_replace( '{title}', $title, $pick['featured_image_prompt'] ),
			'ai_provider'           => 'mock',
			'ai_model'              => 'mock-v1',
			'tokens_used'           => 0,
		);
	}

	/**
	 * Social post content pools organized by content type.
	 *
	 * Each type has multiple variants for variety.
	 * {title} is replaced with the actual topic title at runtime.
	 *
	 * @return array
	 */
	private static function get_social_pools() {

		return array(

			'comparison' => array(
				array(
					'hook_line'      => 'STOP scrolling — this comparison will save you thousands! 🔥',
					'caption'        => "STOP scrolling — this comparison will save you thousands! 🔥\n\n{title} — the question EVERYONE in Pakistan is asking right now.\n\nHere's what most people get WRONG:\n❌ They only look at the price tag\n❌ They ignore real-world battery life\n❌ They skip the camera night mode test\n\nWe tested BOTH phones side by side. The results?\nOne of them is clearly the better deal for Pakistani buyers.\n\n💡 Pro tip: The cheaper one isn't always the worse one.\n\n👉 Full breakdown on hitprice.pk — link in bio!\n\nSave this post before you regret buying the wrong phone. 📌",
					'hashtags'       => '#hitprice #hitpricepk #phonecomparison #mobilepakistan #bestphone2026 #techpk #smartphonepk #camerabattle #budgetphone #pakistantech #samsungvsapple #phonedeals #mobilereview #techreview #gadgetspk',
					'image_text'     => 'Which one wins? The answer will surprise you',
					'carousel_ideas' => array(
						'Slide 1: Both phones side by side — "Which one should you buy?"',
						'Slide 2: Display comparison — size, refresh rate, brightness',
						'Slide 3: Camera samples — day and night shots',
						'Slide 4: Battery life test results',
						'Slide 5: Price in Pakistan — value verdict',
					),
					'cta_text'       => '👉 Full comparison at hitprice.pk — link in bio!',
				),
				array(
					'hook_line'      => 'One costs 30K more — but is it ACTUALLY better? 🤔',
					'caption'        => "One costs 30K more — but is it ACTUALLY better? 🤔\n\n{title}\n\nWe compared:\n📱 Display quality\n📸 Camera in Pakistani lighting conditions\n🔋 Battery with heavy WhatsApp + YouTube use\n⚡ Charging speed\n💰 Actual street price in Lahore & Karachi\n\nThe expensive one lost in 2 categories. Surprised?\n\nDon't waste your money before reading our verdict.\n\n🔗 hitprice.pk has the full breakdown.\n\nTag someone who's deciding between these two! 👇",
					'hashtags'       => '#hitprice #hitpricepk #phonevsphone #mobilepakistan #techcomparison #smartphonereview #bestmobile #phoneguide #pakistantech #budgetvsflags #cameratest #batterylife #techpk #gadgetreview #phonebuyers',
					'image_text'     => '30K price gap — worth it or waste?',
					'carousel_ideas' => array(
						'Slide 1: Price tags side by side — "30K difference. Worth it?"',
						'Slide 2: Spec sheet comparison table',
						'Slide 3: Real camera samples from Lahore streets',
						'Slide 4: Battery drain test — 1hr YouTube, 1hr WhatsApp',
						'Slide 5: Winner badge — our pick and why',
					),
					'cta_text'       => 'Tag a friend who needs this comparison 👇',
				),
			),

			'launch' => array(
				array(
					'hook_line'      => 'Just LAUNCHED in Pakistan — here\'s everything you need to know 📱',
					'caption'        => "Just LAUNCHED in Pakistan — here's everything you need to know 📱\n\n{title}\n\nKey specs that matter:\n✅ Display: [placeholder]\n✅ Camera: [placeholder]\n✅ Battery: [placeholder]\n✅ Price: [placeholder]\n\nFirst impressions?\nThis could be the best phone in its price range this year.\n\nBut there's ONE thing that might be a dealbreaker for some...\n\n📖 Full first look review on hitprice.pk\n\nWould you buy this? Drop a 🔥 or 💩 below!",
					'hashtags'       => '#hitprice #hitpricepk #newlaunch #justlaunched #mobilepakistan #newphone2026 #techlaunch #smartphonepk #pakistantech #phonelaunch #firstlook #unboxing #techpk #mobiledeals #gadgetlaunch',
					'image_text'     => 'JUST LAUNCHED — First look inside',
					'carousel_ideas' => array(
						'Slide 1: Phone hero shot — "Just landed in Pakistan"',
						'Slide 2: Top 5 specs at a glance',
						'Slide 3: What we love about it',
						'Slide 4: The one concern we have',
						'Slide 5: Price and where to buy',
					),
					'cta_text'       => 'Drop 🔥 if you want this phone!',
				),
				array(
					'hook_line'      => 'Pakistan just got a NEW phone — and the price is AGGRESSIVE 💰',
					'caption'        => "Pakistan just got a NEW phone — and the price is AGGRESSIVE 💰\n\n{title}\n\nWhat makes this launch special:\n🔹 Flagship-level camera at mid-range price\n🔹 5000mAh+ battery with fast charging\n🔹 Available in Pakistan from day one\n\nThe competition should be worried.\n\nWe got our hands on it early. Here's the honest take.\n\n👉 hitprice.pk has specs, price, and our verdict.\n\nSave this for later 📌",
					'hashtags'       => '#hitprice #hitpricepk #newphone #launchday #mobilepakistan #phonelaunch2026 #techpk #smartphonedeal #budgetflagship #pakistangadgets #mobilereview #phonebuying #techreview #newrelease #gadgetspk',
					'image_text'     => 'NEW ARRIVAL — Price will surprise you',
					'carousel_ideas' => array(
						'Slide 1: Phone with price tag — "New + Affordable"',
						'Slide 2: Camera sample shots',
						'Slide 3: Battery and charging details',
						'Slide 4: Comparison with closest rival',
						'Slide 5: Buy link and availability',
					),
					'cta_text'       => '👉 Check the full review on hitprice.pk!',
				),
			),

			'upcoming' => array(
				array(
					'hook_line'      => 'LEAKED: This phone is coming to Pakistan and it changes everything 👀',
					'caption'        => "LEAKED: This phone is coming to Pakistan and it changes everything 👀\n\n{title}\n\nHere's what the leaks are saying:\n📱 Expected specs: [placeholder]\n💰 Rumored price in Pakistan: [placeholder]\n📅 Expected launch: [placeholder]\n\n⚠️ Should you wait for this or buy now?\n\nIf these leaks are true, a LOT of people will cancel their current phone plans.\n\n🔔 Follow @hitprice.pk so you don't miss the launch!\n\nWhat do you think? Worth the wait? 👇",
					'hashtags'       => '#hitprice #hitpricepk #leaked #upcoming #mobilepakistan #phoneleaks #techleaks #smartphonepk #waitforit #phonerumors #techpk #2026phones #nextphone #pakistantech #gadgetnews',
					'image_text'     => 'LEAKED — Coming to Pakistan soon',
					'carousel_ideas' => array(
						'Slide 1: Blurred/render phone image — "LEAKED"',
						'Slide 2: Expected spec sheet',
						'Slide 3: Rumored Pakistan price vs competition',
						'Slide 4: Should you wait? Pros and cons',
						'Slide 5: Follow for launch updates',
					),
					'cta_text'       => 'Follow @hitprice.pk to get notified first! 🔔',
				),
			),

			'tips' => array(
				array(
					'hook_line'      => '90% of Pakistanis don\'t know this phone trick — do you? 🤯',
					'caption'        => "90% of Pakistanis don't know this phone trick — do you? 🤯\n\n{title}\n\nThese simple tricks can:\n✅ Double your battery life\n✅ Free up storage instantly\n✅ Make your phone 2x faster\n✅ Protect your data from hackers\n\nMost people never change these settings.\nYour phone has been BEGGING you to fix these.\n\n💡 Full guide with screenshots on hitprice.pk\n\nShare this with someone who complains about their slow phone 😂👇",
					'hashtags'       => '#hitprice #hitpricepk #phonetips #mobiletricks #techtips #smartphonetips #androidtips #iphonetips #phonehacks #pakistantech #mobilehelp #techpk #phonespeed #batterylife #storagetips',
					'image_text'     => 'Phone tricks 90% people don\'t know',
					'carousel_ideas' => array(
						'Slide 1: "Your phone is hiding these settings"',
						'Slide 2: Battery saver settings walkthrough',
						'Slide 3: Storage cleanup trick',
						'Slide 4: Performance boost setting',
						'Slide 5: Security tip most people skip',
					),
					'cta_text'       => 'Share with a friend who needs this! 👇',
				),
			),

			'budget' => array(
				array(
					'hook_line'      => 'Best phones under 50,000 PKR — stop wasting money on wrong picks 💸',
					'caption'        => "Best phones under 50,000 PKR — stop wasting money on wrong picks 💸\n\n{title}\n\nWe tested 10+ phones in this budget. Only 3 made the cut.\n\n🥇 Best Overall: [placeholder]\n🥈 Best Camera: [placeholder]\n🥉 Best Battery: [placeholder]\n\n❌ Phones we do NOT recommend (even though they're popular)\n\nPakistan mein sab se zyada galat phone isi budget mein khareedtay hain.\n\nDon't be that person.\n\n📖 Full list with prices on hitprice.pk\n\nSave this before your next purchase 📌",
					'hashtags'       => '#hitprice #hitpricepk #bestphones #under50k #budgetphones #mobilepakistan #phoneguide #smartphonebudget #bestvalue #techpk #phonedeals #pakistantech #mobilereview #topphones #affordablephones',
					'image_text'     => 'Under 50K — Only 3 phones worth buying',
					'carousel_ideas' => array(
						'Slide 1: "Under 50K — Which one?"',
						'Slide 2: #1 Best Overall — key specs and price',
						'Slide 3: #2 Best Camera — sample shots',
						'Slide 4: #3 Best Battery — screen-on time',
						'Slide 5: Phones to AVOID in this range',
					),
					'cta_text'       => 'Save this list before buying! 📌',
				),
			),

			'deal' => array(
				array(
					'hook_line'      => '🚨 PRICE DROP ALERT — This phone just got massively cheaper in Pakistan!',
					'caption'        => "🚨 PRICE DROP ALERT — This phone just got massively cheaper in Pakistan!\n\n{title}\n\n💰 Old price: [placeholder]\n💰 New price: [placeholder]\n📉 You save: [placeholder]\n\nThis is NOT a sale gimmick. This is a permanent price revision.\n\nAt this new price, it DESTROYS everything else in its category.\n\n⚠️ Stock is limited. Pakistani retailers confirmed.\n\n🔗 Check current price on hitprice.pk — link in bio!\n\nTag someone who's been waiting for this deal! 🏷️",
					'hashtags'       => '#hitprice #hitpricepk #pricedrop #phonedeal #mobilepakistan #dealalert #techdeals #smartphonedeal #bestprice #pakistantech #budgetphone #phonebuying #techpk #savemoney #mobiledeal',
					'image_text'     => 'PRICE DROP — Biggest deal this month',
					'carousel_ideas' => array(
						'Slide 1: Phone with old price crossed out, new price highlighted',
						'Slide 2: What you get at this price — spec highlights',
						'Slide 3: Comparison with rivals at same price now',
						'Slide 4: Where to buy + availability',
						'Slide 5: "Deal won\'t last — act now"',
					),
					'cta_text'       => 'Tag someone who needs this deal! 🏷️',
				),
			),
		);
	}

	/**
	 * Blog post content pools organized by content type.
	 *
	 * @return array
	 */
	private static function get_blog_pools() {

		return array(

			'comparison' => array(
				array(
					'title'                 => '{title} — Full Comparison for Pakistani Buyers (2026)',
					'excerpt'               => 'A detailed side-by-side comparison covering display, camera, battery, performance, and price in Pakistan to help you pick the right phone.',
					'meta_title'            => '{title} Comparison Pakistan 2026 — HitPrice',
					'meta_description'      => 'Detailed {title} comparison for Pakistan. We compare display, camera, battery life, and price to help you choose the best phone for your budget.',
					'featured_image_prompt' => 'Clean product photography of two smartphones side by side on a white surface, minimal style, comparison layout',
					'content'               => "<h2>Introduction</h2>\n<p>Choosing between these two phones? You're not alone. <strong>{title}</strong> is one of the most searched comparisons in Pakistan right now. In this guide, we break down every detail that matters to Pakistani buyers — from camera quality in our lighting conditions to real-world battery life with heavy WhatsApp and YouTube use.</p>\n\n<h2>Display</h2>\n<p>[Placeholder: Display size, resolution, refresh rate, brightness comparison. Include which one is better for outdoor use in Pakistani sunlight.]</p>\n\n<h2>Camera</h2>\n<p>[Placeholder: Main camera, ultrawide, night mode comparison. Include sample scenarios relevant to Pakistani users — low-light streets, food photography, family gatherings.]</p>\n\n<h2>Battery Life</h2>\n<p>[Placeholder: Battery capacity, real-world screen-on time, charging speed. Test with Pakistani usage patterns — WhatsApp, YouTube, social media heavy use.]</p>\n\n<h2>Performance</h2>\n<p>[Placeholder: Processor, RAM, storage. Gaming performance. App loading speeds.]</p>\n\n<h2>Price in Pakistan</h2>\n<p>[Placeholder: Official price, street price in Lahore/Karachi/Islamabad. Which one offers better value for money?]</p>\n\n<h2>Verdict</h2>\n<p>[Placeholder: Our recommendation based on different buyer profiles — budget-conscious, camera-focused, performance-first.]</p>",
				),
			),

			'launch' => array(
				array(
					'title'                 => '{title} — Price, Specs & First Impressions in Pakistan',
					'excerpt'               => 'Everything you need to know about this new phone launch in Pakistan — specs, price, availability, and our honest first impressions.',
					'meta_title'            => '{title} Launch Pakistan — Price & Specs — HitPrice',
					'meta_description'      => '{title} has launched in Pakistan. Check the official price, full specs, availability, and our first impressions in this detailed coverage.',
					'featured_image_prompt' => 'New smartphone unboxing on a clean desk, premium feel, product launch photography style',
					'content'               => "<h2>Launch Overview</h2>\n<p><strong>{title}</strong> has officially arrived in Pakistan. Here's everything you need to know before making a purchase decision.</p>\n\n<h2>Key Specifications</h2>\n<p>[Placeholder: Full spec table — display, processor, RAM, storage, camera system, battery, charging, OS.]</p>\n\n<h2>Price in Pakistan</h2>\n<p>[Placeholder: Official retail price. Expected street prices. EMI/installment options if available.]</p>\n\n<h2>What We Like</h2>\n<p>[Placeholder: Top 3-4 strengths of the device for Pakistani market.]</p>\n\n<h2>What Could Be Better</h2>\n<p>[Placeholder: 1-2 concerns or compromises.]</p>\n\n<h2>Should You Buy It?</h2>\n<p>[Placeholder: Recommendation based on competition in same price bracket in Pakistan.]</p>",
				),
			),

			'upcoming' => array(
				array(
					'title'                 => '{title} — Expected Price, Specs & Launch Date in Pakistan',
					'excerpt'               => 'All the leaks and rumors about this upcoming phone — what to expect for price, specs, and availability in Pakistan.',
					'meta_title'            => '{title} Leaks Pakistan — Expected Price & Specs — HitPrice',
					'meta_description'      => 'Latest leaks about {title} for Pakistan. Expected price, rumored specs, launch date, and whether you should wait for it or buy something else now.',
					'featured_image_prompt' => 'Mysterious smartphone silhouette with question marks, tech leak concept art, dark background with blue highlights',
					'content'               => "<h2>What We Know So Far</h2>\n<p>The rumor mill is buzzing about <strong>{title}</strong>. Here's a roundup of the most credible leaks and what they mean for Pakistani buyers.</p>\n\n<h2>Expected Specifications</h2>\n<p>[Placeholder: Rumored specs from reliable leakers.]</p>\n\n<h2>Expected Price in Pakistan</h2>\n<p>[Placeholder: Price estimation based on global pricing and Pakistan markup patterns.]</p>\n\n<h2>Expected Launch Date</h2>\n<p>[Placeholder: Rumored global and Pakistan launch timeline.]</p>\n\n<h2>Should You Wait?</h2>\n<p>[Placeholder: Compare with currently available alternatives. Advise on whether waiting makes sense.]</p>",
				),
			),

			'tips' => array(
				array(
					'title'                 => '{title} — Essential Tips Every Pakistani Phone User Should Know',
					'excerpt'               => 'Simple but powerful tips and tricks to get more out of your smartphone — better battery, more storage, faster performance.',
					'meta_title'            => '{title} — Phone Tips Pakistan — HitPrice',
					'meta_description'      => '{title}. Learn how to boost battery life, free up storage, speed up your phone, and protect your data with these simple tricks.',
					'featured_image_prompt' => 'Smartphone with glowing tip icons around it, helpful tech tips concept, bright and friendly illustration style',
					'content'               => "<h2>Why These Tips Matter</h2>\n<p>Most people in Pakistan use their phones for 4-6 hours daily but never optimize the settings. These simple changes to your phone can make a huge difference.</p>\n\n<h2>Tip 1: Battery Optimization</h2>\n<p>[Placeholder: Step-by-step battery saving tips relevant to Pakistani usage — WhatsApp, YouTube, social media.]</p>\n\n<h2>Tip 2: Free Up Storage</h2>\n<p>[Placeholder: How to clear WhatsApp media, cache, and unused apps.]</p>\n\n<h2>Tip 3: Speed Boost</h2>\n<p>[Placeholder: Developer options trick, animation scale, background process limits.]</p>\n\n<h2>Tip 4: Security</h2>\n<p>[Placeholder: Two-factor auth, app permissions, avoiding fake apps from third-party stores.]</p>",
				),
			),

			'budget' => array(
				array(
					'title'                 => '{title} — Best Phones for Every Budget in Pakistan (2026)',
					'excerpt'               => 'Our tested and ranked list of the best phones you can buy in Pakistan right now at different price points.',
					'meta_title'            => '{title} — Best Budget Phones Pakistan 2026 — HitPrice',
					'meta_description'      => '{title}. Tested and ranked list of best phones in Pakistan across every budget — from under 25K to premium flagships. Updated for 2026.',
					'featured_image_prompt' => 'Multiple smartphones arranged by price, ascending order, clean white background, budget to premium gradient',
					'content'               => "<h2>How We Picked</h2>\n<p>We tested over 15 phones available in Pakistan. Each phone was evaluated on camera quality, battery life with heavy Pakistani usage patterns, display quality, and value for money.</p>\n\n<h2>Best Under 25,000 PKR</h2>\n<p>[Placeholder: Top pick with brief reasoning.]</p>\n\n<h2>Best Under 50,000 PKR</h2>\n<p>[Placeholder: Top pick with brief reasoning.]</p>\n\n<h2>Best Under 75,000 PKR</h2>\n<p>[Placeholder: Top pick with brief reasoning.]</p>\n\n<h2>Best Under 100,000 PKR</h2>\n<p>[Placeholder: Top pick with brief reasoning.]</p>\n\n<h2>Phones to Avoid</h2>\n<p>[Placeholder: Popular but overpriced or underperforming phones in Pakistan.]</p>",
				),
			),

			'deal' => array(
				array(
					'title'                 => '{title} — Price Drop Alert for Pakistani Buyers',
					'excerpt'               => 'Major price drop alert! This phone just got significantly cheaper in Pakistan. Here\'s why it\'s now the best deal in its category.',
					'meta_title'            => '{title} Price Drop Pakistan — Best Deal Now — HitPrice',
					'meta_description'      => '{title} — massive price drop in Pakistan. Check the new price, what you get, and why this is now the best deal in its price range.',
					'featured_image_prompt' => 'Smartphone with a red price drop arrow, deal alert concept, bold typography, shopping/savings theme',
					'content'               => "<h2>The Price Drop</h2>\n<p><strong>{title}</strong> — this phone just received a significant price revision in Pakistan, making it one of the best deals available right now.</p>\n\n<h2>New vs Old Price</h2>\n<p>[Placeholder: Old price, new price, savings amount, where the price dropped.]</p>\n\n<h2>Why This Matters</h2>\n<p>[Placeholder: At the new price point, how does it compare to competition? What alternatives does it now beat?]</p>\n\n<h2>Where to Buy</h2>\n<p>[Placeholder: Availability in Pakistan — online and offline retailers, installment options.]</p>\n\n<h2>Should You Buy Now?</h2>\n<p>[Placeholder: Is this the lowest it'll go? Or should you wait for further drops?]</p>",
				),
			),
		);
	}
}
