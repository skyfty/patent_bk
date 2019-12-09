
CREATE TABLE IF NOT EXISTS `__PREFIX__calendar`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL COMMENT '管理员ID',
  `title` varchar(100) NOT NULL COMMENT '任务标题',
  `url` varchar(100) NOT NULL COMMENT '链接',
  `starttime` int(10) NOT NULL COMMENT '开始时间',
  `endtime` int(10) NOT NULL COMMENT '结束时间',
  `background` varchar(10) NOT NULL COMMENT '背景颜色',
  `classname` varchar(30) NOT NULL COMMENT '自定义类名',
  `createtime` int(10) NOT NULL COMMENT '创建时间',
  `updatetime` int(10) NOT NULL COMMENT '更新时间',
  `status` enum('normal','hidden','expired','completed') NOT NULL COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8 COMMENT='日历表';

BEGIN;
INSERT INTO `__PREFIX__calendar` VALUES (148, 1, '外部链接事件', 'http://www.baidu.com', 1505059200, 1505059200, '#f012be', '', 1505125111, 1505125111, 'normal');
INSERT INTO `__PREFIX__calendar` VALUES (149, 1, '新选项卡事件', 'dashboard', 1505235600, 1505235600, '#e74c3c', 'btn-addtabs', 1505125095, 1505125559, 'normal');
INSERT INTO `__PREFIX__calendar` VALUES (150, 1, '弹窗事件', 'general/profile', 1505498400, 1505539800, '#0073b7', 'btn-dialog', 1505125066, 1505125555, 'completed');
INSERT INTO `__PREFIX__calendar` VALUES (151, 1, '普通事件', '', 1506009600, 1506009600, '#18bc9c', '', 1505125044, 1505125044, 'normal');
INSERT INTO `__PREFIX__calendar` VALUES (152, 1, '普通事件', '', 1505244600, 1505428200, '#18bc9c', '', 1505125044, 1505125575, 'normal');
INSERT INTO `__PREFIX__calendar` VALUES (153, 1, '新选项卡事件', 'dashboard', 1506009600, 1506009600, '#e74c3c', 'btn-addtabs', 1505125095, 1505125095, 'normal');
INSERT INTO `__PREFIX__calendar` VALUES (154, 1, '外部链接事件', 'http://www.baidu.com', 1506009600, 1506009600, '#f012be', '', 1505125111, 1505125111, 'normal');
INSERT INTO `__PREFIX__calendar` VALUES (155, 1, '新选项卡事件', 'dashboard', 1505491200, 1505491200, '#e74c3c', 'btn-addtabs', 1505125095, 1505125095, 'normal');
INSERT INTO `__PREFIX__calendar` VALUES (156, 1, '新选项卡事件', 'dashboard', 1504886400, 1504886400, '#e74c3c', 'btn-addtabs', 1505125095, 1505125095, 'normal');
INSERT INTO `__PREFIX__calendar` VALUES (157, 1, '新选项卡事件', 'dashboard', 1504713600, 1505059200, '#e74c3c', 'btn-addtabs', 1505125095, 1505125631, 'completed');
INSERT INTO `__PREFIX__calendar` VALUES (158, 1, '弹窗事件', 'general/profile', 1504713600, 1504713600, '#0073b7', 'btn-dialog', 1505125066, 1505125066, 'completed');
INSERT INTO `__PREFIX__calendar` VALUES (159, 1, '弹窗事件', 'general/profile', 1505232000, 1505232000, '#0073b7', 'btn-dialog', 1505125066, 1505125066, 'normal');
COMMIT;

CREATE TABLE IF NOT EXISTS `__PREFIX__calendar_event`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL COMMENT '管理员ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '任务标题',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '链接',
  `background` varchar(10) NOT NULL COMMENT '背景颜色',
  `classname` varchar(30) NOT NULL COMMENT '自定义类名',
  `createtime` int(10) NOT NULL COMMENT '添加时间',
  `updatetime` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COMMENT='事件表';

BEGIN;
INSERT INTO `__PREFIX__calendar_event` VALUES (42, 1, '普通事件', '', '#18bc9c', '', 1505125044, 1505125044);
INSERT INTO `__PREFIX__calendar_event` VALUES (43, 1, '弹窗事件', 'general/profile', '#0073b7', 'btn-dialog', 1505125066, 1505125066);
INSERT INTO `__PREFIX__calendar_event` VALUES (44, 1, '新选项卡事件', 'dashboard', '#e74c3c', 'btn-addtabs', 1505125095, 1505125095);
INSERT INTO `__PREFIX__calendar_event` VALUES (45, 1, '外部链接事件', 'http://www.baidu.com', '#f012be', '', 1505125111, 1505125111);
COMMIT;