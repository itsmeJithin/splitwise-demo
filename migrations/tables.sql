CREATE TABLE `users`
(
    `user_id`      varchar(255) NOT NULL,
    `name`         varchar(255) NULL DEFAULT NULL,
    `phone_number` varchar(255) NOT NULL,
    PRIMARY KEY (user_id)
);


CREATE TABLE `sample`.`user_expenses` (
                                          `id` int AUTO_INCREMENT,
                                          `user_id` varchar(255),
                                          `owed_user_id` varchar(255),
                                          `total_amount` decimal (10,
                                                   2),
                                          `description` varchar(255),
                                          `user_owe_amount` decimal(10,2),
                                          `created_on` datetime DEFAULT NOW(),
                                          `is_settled` boolean DEFAULT FALSE,
                                          `settled_on` datetime,
                                          PRIMARY KEY (id)
);