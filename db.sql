/*
Navicat MySQL Data Transfer

Source Server         : 本地数据库
Source Server Version : 50719
Source Host           : localhost:3306
Source Database       : draw_new_users

Target Server Type    : MYSQL
Target Server Version : 50719
File Encoding         : 65001

Date: 2019-02-28 13:50:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `admin`
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL COMMENT '用户名称',
  `phone` varchar(11) NOT NULL COMMENT '手机号',
  `password` varchar(128) NOT NULL,
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 0禁用  1正常',
  `create_time` datetime DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', 'coolong', '18833111093', '$2y$10$MY9wEvu/3kZC7a11q8zmyOZtN.IzcvHETWsatV3yeaJdJo3219bNK', '1', null, '3');
INSERT INTO `admin` VALUES ('3', 'xiaomubobo', '18888888888', '$2y$10$o4edIO0712eaTd.4s5vtgekRIP6SuTkBz13XGpfx0VTbq3Kkd7lTa', '1', null, '1');

-- ----------------------------
-- Table structure for `menu`
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `pid` int(11) NOT NULL,
  `url` varchar(128) NOT NULL,
  `icon` varchar(128) NOT NULL,
  `sort` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `create_time` datetime DEFAULT NULL,
  `remark` varchar(255) NOT NULL,
  `is_menu` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES ('1', '首页', '0', '/', 'fa fa-home', '0', '1', null, '', '1');
INSERT INTO `menu` VALUES ('2', '系统管理', '0', '', 'fa-shopping-cart', '0', '1', '2019-02-28 11:37:57', '', '1');
INSERT INTO `menu` VALUES ('3', '管理员管理', '2', '/admin', 'fa fa-building', '0', '1', '2019-02-28 11:38:14', '', '1');
INSERT INTO `menu` VALUES ('4', '菜单管理', '2', '/menu', 'fa fa-shopping-bag', '0', '1', '2019-02-28 11:38:33', '', '1');
INSERT INTO `menu` VALUES ('5', '角色管理', '2', '/role', 'fa-shopping-cart', '0', '1', '2019-02-28 11:38:48', '', '1');

-- ----------------------------
-- Table structure for `role`
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `menu_id` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `tree_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES ('1', '管理员', null, null, '1', null, null, null);
INSERT INTO `role` VALUES ('2', '12', '1', null, '1', null, '', '1');
INSERT INTO `role` VALUES ('3', '可爱图图', '1,2,3,4,5', null, '1', null, '', '1,2,3,4,5');
