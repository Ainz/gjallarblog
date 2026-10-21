Gjallarblog **0.04-alpha.1** groups settings into journal identity, layout and placement, entry details, and site navigation. Choose Hidden, Before content or After content separately for home and album pages. The journal link has its own visibility control and label; ordering remains in Piwigo’s menu manager.

Journal RSS links are removed. The old feed address redirects to Piwigo’s core feed; journal posts are not added to that photo feed.

Settings → Plugin removal shows a live entry count and requires confirmation. Data is kept by default. A webmaster can explicitly erase entries and settings by typing Gjallarblog. The screen verifies that the plugin directory is gone before erasing data or claiming success. Piwigo deactivation and uninstall preserve data for reinstalling. Piwigo’s own delete action removes only the plugin files and preserves data; use the plugin’s counted removal screen when you want data erased.

Download **gjallarblog-0.04-alpha.1.zip** and verify **SHA256SUMS.txt**. Back up your gallery and replace the plugin directory. Existing display choices migrate to equivalent placement values. This remains an experimental prerelease; see README and validation documentation for tested combinations and limitations.
