create database dotinstall_instagram_api_php;
grant all on dotinstall_instagram_api_php.* to dbuser@localhost identified by 'aiqUbL5V';
use dotinstall_instagram_api_php

create table users(
	id int not null auto_increment primary key,
	instagram_user_id int unique,
	instagram_user_name varchar(255),
	full_name varchar(255),
	instagram_profile_picture varchar(255),
	bio varchar(255),
	website varchar(255),
	instagram_access_token varchar(255),
	media int,
	follows int,
	followed_by int,
	created datetime,
	modified datetime
	);

