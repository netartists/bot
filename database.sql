CREATE TABLE `depot` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` varchar(255),
	`provider` varchar(255),
	`buyDate` TIMESTAMP,
	`sellDate` TIMESTAMP,
	`buyPrice` FLOAT,
	`sellPrice` FLOAT,
	`stopPrice` FLOAT,
	`tradingReason` varchar(255),
	`trailingStopDistance` FLOAT,
	PRIMARY KEY (`id`)
);