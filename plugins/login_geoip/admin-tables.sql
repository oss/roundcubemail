-- This table should be in a database with ONLY insert privs

CREATE TABLE IF NOT EXISTS geoip_admin_real (
   id int(11) NOT NULL auto_increment,
   ip int(11) unsigned not NULL,
   host varchar(255) not null,
   username varchar(64) not null,
   loc varchar(8) not null,
   flag varchar(16) not null,
   description varchar(128) not null,
   time datetime not null,
   exemption bool,
   PRIMARY KEY (id)
);

CREATE VIEW geoip_admin AS SELECT id, INET_NTOA(ip), host, username, loc, flag, description, time, exemption FROM geoip_admin_real;

