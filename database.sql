CREATE TABLE `depot` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` varchar(255),
	`wkn` varchar(255),
	`provider` varchar(255),
	`buyPrice` FLOAT,
	`sellPrice` FLOAT,
	`stopPrice` FLOAT,
	`trailingStopDistance` FLOAT,
	PRIMARY KEY (`id`)
);