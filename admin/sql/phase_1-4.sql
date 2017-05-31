INSERT INTO `menu` (`id`, `parent`, `rank`, `hide`, `icon`, `code`, `name`) VALUES
(83, 47, 0, 1, '', 'member_bl_member', '學員管理'),
(84, 47, 0, 1, '', 'member_bl_consultant', '學員管理'),
(85, 0, 548, 0, '', 'elective_mgr', '選修課程管理');

--
-- 資料表結構 `member_bl_consultant`
--

CREATE TABLE IF NOT EXISTS `member_bl_consultant` (
  `id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `modifier` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `cdate` datetime NOT NULL,
  `mdate` datetime NOT NULL,
  `member_id` int(11) NOT NULL,
  `black_id` int(11) NOT NULL,
  `memo` text COLLATE utf8_unicode_ci NOT NULL,
  `deleted` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `member_bl_member`
--

CREATE TABLE IF NOT EXISTS `member_bl_member` (
  `id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `modifier` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `cdate` datetime NOT NULL,
  `mdate` datetime NOT NULL,
  `member_id` int(11) NOT NULL,
  `black_id` int(11) NOT NULL,
  `memo` text COLLATE utf8_unicode_ci NOT NULL,
  `deleted` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `member_bl_consultant`
--
ALTER TABLE `member_bl_consultant`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `member_bl_member`
--
ALTER TABLE `member_bl_member`
  ADD PRIMARY KEY (`id`);

--
-- 在匯出的資料表使用 AUTO_INCREMENT
--

--
-- 使用資料表 AUTO_INCREMENT `member_bl_consultant`
--
ALTER TABLE `member_bl_consultant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `member_bl_member`
--
ALTER TABLE `member_bl_member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
