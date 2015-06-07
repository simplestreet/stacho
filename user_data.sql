
create table user_data(
  id int not null auto_increment primary key,
  user_id int not null,
  image_id varchar(255) not null unique,
  image_url varchar(255) unique,
  link varchar(255) unique,
  caption text,
  tags varchar(255),
  video boolean,
  created datetime
);
create table cache_user_data(
  id int not null auto_increment primary key,
  user_id int not null,
  image_id varchar(255) not null unique,
  image_url varchar(255) unique,
  link varchar(255) unique,
  caption text,
  tags varchar(255),
  video boolean,
  created datetime
);

