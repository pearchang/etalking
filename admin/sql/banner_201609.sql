INSERT INTO `menu` (`id`, `parent`, `rank`, `hide`, `icon`, `code`, `name`) VALUES (82, 0, 355, 0, '', 'banner', '官網banner');

CREATE TABLE IF NOT EXISTS `banner` (
  `id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `modifier` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `cdate` datetime NOT NULL,
  `mdate` datetime NOT NULL,
  `name` varchar(240) COLLATE utf8_unicode_ci NOT NULL,
  `url` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `banner_images`
--

CREATE TABLE IF NOT EXISTS `banner_images` (
  `id` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `parent_type` tinyint(4) NOT NULL,
  `type` enum('gif','png','jpg') COLLATE utf8_unicode_ci NOT NULL,
  `tag` smallint(6) NOT NULL,
  `thumb` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `origin` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `text` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `banner_images`
--
ALTER TABLE `banner_images`
  ADD PRIMARY KEY (`id`);

--
-- 在匯出的資料表使用 AUTO_INCREMENT
--

--
-- 使用資料表 AUTO_INCREMENT `banner`
--
ALTER TABLE `banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `banner_images`
--
ALTER TABLE `banner_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;