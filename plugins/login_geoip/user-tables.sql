-- These tables should be in a database with standard priveleges

CREATE TABLE IF NOT EXISTS geoip_user_real (
   id int(11) NOT NULL auto_increment,
   ip int(11) unsigned not null,
   host varchar(255) not null,
   username varchar(64) not null,
   loc varchar(8) not null,
   flag varchar(16) not null,
   description varchar(128) not null,
   time datetime not null,
   exemption bool,
   PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS geoip_cidr_real (
   id int(11) not null auto_increment,
   net int(10) unsigned not null,
   mask int(11) not null,
   host varchar(255) not null,
   description varchar(128) not null,
   flag varchar(64),
   primary key (id)
);

CREATE TABLE IF NOT EXISTS geoip_exemption (
   id int(11) not null auto_increment,
   start datetime not null,
   end datetime not null,
   identity varchar(64) not null,
   netid varchar(8) not null,
   loc varchar(8) not null,   
   primary key (id)
);

CREATE VIEW geoip_user AS SELECT id, INET_NTOA(ip), host, username, loc, flag, description, time, exemption FROM geoip_user_real;
CREATE VIEW geoip_cidr AS SELECT id, INET_NTOA(net), mask, host, description, flag FROM geoip_cidr_real;

insert into geoip_exemption (start,end,identity,netid,loc) VALUES (now(), '2011-08-14', 'rfranknj@eden.rutgers.edu', 'rfranknj', 'GB');
insert into geoip_cidr_real (net, mask, host, description, flag) VALUES (INET_ATON('192.168.227.0'),24,'OSS','Hill 128','jla.ico');
insert into geoip_cidr_real (net, mask, host, description, flag) VALUES (INET_ATON('10.0.0.0'),8,'Rutgers','Rutgers University','rutgers.gif');
insert into geoip_cidr_real (net, mask, host, description, flag) VALUES (INET_ATON('192.168.0.0'),16,'Rutgers','Rutgers University','rutgers.gif');
insert into geoip_cidr_real (net, mask, host, description, flag) VALUES (INET_ATON('172.16.0.0'),12,'Rutgers','Rutgers University','rutgers.gif');

