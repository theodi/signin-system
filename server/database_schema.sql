create table people (
	id varchar(32) not null PRIMARY KEY,
	firstname varchar(255),
	lastname varchar(255),
	email varchar(255),
	company varchar(255),
	sector varchar(255)
);
create table in_out (
	id varchar(32) not null,
	checkin varchar(255),
	checkout varchar(255)
);
create table eventbrite_attendees (
	person_id varchar(32) not null,
	event_id varchar(32),
	checkin varchar(255)
);
create table people_keycards ( 
	person_id varchar(32) not null,
	keycard_id varchar(255) not null
);
create table member_keycards ( 
	member_id varchar(32) not null,
	keycard_id varchar(255) not null
);
create table people_roles (
	person_id varchar(32) not null,
	role varchar(32) not null,
	valid_from TIMESTAMP DEFAULT CURRENT_TIMESTAMP not null	
);
