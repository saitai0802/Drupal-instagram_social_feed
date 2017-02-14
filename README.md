# Drupal-instagram_social_feed
This is drupal module that allows you to create Instagram feeds for your site that will download images from Instagram using filtering by hashtags.  Inspired by exsisting drupal 7 module "Instagram Feeds"

This module is inspried by <a href="https://www.drupal.org/project/instagram_feeds" target="_blank">Instagram Feeds</a>. 
Basically, the main core functions in "Instagram Feeds" are included in this module, but some of the function calls have already been deprecated by Instagram(5/10/2015).<br/><br/>
And some new features have been added to this module as well<br/>
1.Creating unlimited number of Instagram Feeds (that will be available as blocks) with custom settings for each one (custom node type)<br/>
2.Downloading new Instagram images by cron<br/>
3.Deleting old images by cron (expiration time sets up at Instagram Feeds settings page)<br/>
4.Hiding images that flagged by users as inappropriate until moderator's check.<br/>
5.A comment box to let admin user to leave a comment on each feed.<br/>

Once again, since Instagram changes their API policy very often. That may case some of the fucntion calls in this module maybe dropped out already.<br/>
**Please modify those parts if needed.**

###Configuration
1. Put this module into your Drupal modules directory
2. Enable the Instagram Feeds (and Instagram Feeds Moderation) modules: Administration > Modules (admin/modules)
3. Fill Client ID and Client Secret fields from created for your site Instagram Client. Save changes and then click to the link under Access Token (in the Description to the field) to generate it.
4. Setting hash tag you want to fetch.
5. Setup a view that output those hash tag records to a View.

Module Screenshot for reference
---
**Dashboard page**:<br/>
![Dashboard page](https://github.com/saitai0802/Drupal-instagram_social_feed/blob/master/img/Step1.JPG)

**Management page**:<br/>
![Management page](https://github.com/saitai0802/Drupal-instagram_social_feed/blob/master/img/Step2.JPG)

**Getting images from front-end**:<br/>
![Front-end](https://github.com/saitai0802/Drupal-instagram_social_feed/blob/master/img/Step3.png)
