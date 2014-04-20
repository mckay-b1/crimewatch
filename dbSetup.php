<?php
    require_once('config.php');
    
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_errno) {
        die("ERROR: Database connection failed! ".$mysqli->connect_errno);
    }
    
    if (!$mysqli->select_db(DB_NAME)) {
        if ($mysqli->query("CREATE DATABASE ".DB_NAME)) {
            echo "Database created successfully!";
        } else {
            echo "ERROR: Database creation failed! ".mysql_error();
        }
    }
    
    $tables_sql = array();
    
    $tables_sql []= "CREATE TABLE users (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	email VARCHAR(100) NOT NULL,
	password VARCHAR(32) NOT NULL,
	firstname VARCHAR(100) NOT NULL,
	registration_date DATETIME NOT NULL,
    activation_code VARCHAR(32) NOT NULL,
	confirmed INT(1) UNSIGNED NOT NULL,
	last_login DATETIME NOT NULL,
	PRIMARY KEY (id)
    )
    COLLATE='utf8_general_ci'
    ENGINE=InnoDB;";
    
    $tables_sql []= "CREATE TABLE IF NOT EXISTS locations (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	user_id INT(10) UNSIGNED NOT NULL,
	name VARCHAR(255) NOT NULL,
	address VARCHAR(255) NOT NULL,
        PRIMARY KEY (id),
        INDEX FK_locations_users (user_id),
        CONSTRAINT FK_locations_users FOREIGN KEY (user_id) REFERENCES users (id)
    )
    COLLATE='utf8_general_ci'
    ENGINE=InnoDB;";
        
//    $tables_sql []= "CREATE TABLE IF NOT EXISTS custom_areas (
//        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
//        user_id INT(10) UNSIGNED NOT NULL,
//        name VARCHAR(100) NOT NULL,
//        PRIMARY KEY (id),
//        INDEX FK_custom_areas_users (user_id),
//        CONSTRAINT FK_custom_areas_users FOREIGN KEY (user_id) REFERENCES users (id)
//    )
//    COLLATE='utf8_general_ci'
//    ENGINE=InnoDB;";
//            
//    $tables_sql []= "CREATE TABLE IF NOT EXISTS custom_areas_points (
//	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
//	area_id INT(10) UNSIGNED NOT NULL,
//	lat FLOAT NOT NULL,
//	lng FLOAT NOT NULL,
//	PRIMARY KEY (id),
//	INDEX FK_custom_areas_points_custom_areas (area_id),
//	CONSTRAINT FK_custom_areas_points_custom_areas FOREIGN KEY (area_id) REFERENCES custom_areas (id)
//    )
//    COLLATE='utf8_general_ci'
//    ENGINE=InnoDB;";
    
    foreach ($tables_sql as $sql) {
        if(!$mysqli->query($sql)){
            echo "ERROR: Failed to create table! ".$dbConnect->error." (".$dbConnect->errno.")";
        }
    }

    $mysqli->close();