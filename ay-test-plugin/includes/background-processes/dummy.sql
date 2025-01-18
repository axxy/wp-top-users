-- Configuration variables
SET @users_to_generate = 9999;  -- Number of users to generate
SET @orders_to_generate = 100000;  -- Number of orders to generate
SET @min_orders_per_user = 5;    -- Minimum orders per user
SET @max_orders_per_user = 15;   -- Maximum orders per user

-- Create orders table if it doesn't exist
CREATE TABLE IF NOT EXISTS wp_orders (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    order_id varchar(255) NOT NULL,
    user_id bigint(20) NOT NULL,
    order_total decimal(10,2) NOT NULL,
    order_date datetime NOT NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY order_date (order_date)
) ENGINE=InnoDB;

-- Drop procedures if they exist
DROP PROCEDURE IF EXISTS GenerateUsers;
DROP PROCEDURE IF EXISTS GenerateOrders;

DELIMITER //

CREATE PROCEDURE GenerateUsers()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE current_max_id BIGINT;
    
    -- Get the current maximum ID
    SELECT COALESCE(MAX(ID), 0) INTO current_max_id FROM wp_users;
    SET i = current_max_id + 1;
    
    REPEAT
        INSERT INTO wp_users (user_login, user_pass, user_nicename, user_email, user_registered, display_name)
        SELECT 
            CONCAT('user', i) as user_login,
            CONCAT('$2y$10$dummyhashedpassword', i) as user_pass,
            CONCAT('user', i) as user_nicename,
            CONCAT('user', i, '@example.com') as user_email,
            NOW() as user_registered,
            CONCAT('User ', i) as display_name;
            
        SET i = i + 1;
    UNTIL i > (current_max_id + @users_to_generate) END REPEAT;
END;
//

CREATE PROCEDURE GenerateOrders()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE current_user_id BIGINT;
    DECLARE order_count INT;
    DECLARE orders_for_user INT;
    DECLARE i INT;
    DECLARE user_cursor CURSOR FOR SELECT ID FROM wp_users;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    SET order_count = 1;
    
    OPEN user_cursor;
    
    read_loop: LOOP
        FETCH user_cursor INTO current_user_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Generate random number of orders for this user
        SET orders_for_user = @min_orders_per_user + FLOOR(RAND() * (@max_orders_per_user - @min_orders_per_user + 1));
        
        -- Generate orders for this user
        SET i = 1;
        WHILE i <= orders_for_user AND order_count <= @orders_to_generate DO
            INSERT INTO wp_orders (order_id, user_id, order_total, order_date)
            VALUES (
                CONCAT('Order', DATE_FORMAT(NOW(), '%Y'), LPAD(order_count, 8, '0')),
                current_user_id,
                ROUND(RAND() * 1000 + 0.99, 2),
                DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 365) DAY)
            );
            
            SET i = i + 1;
            SET order_count = order_count + 1;
        END WHILE;
        
        IF order_count > @orders_to_generate THEN
            LEAVE read_loop;
        END IF;
    END LOOP;
    
    CLOSE user_cursor;
END;
//

DELIMITER ;

-- Execute procedures
CALL GenerateUsers();
CALL GenerateOrders();

-- Clean up
DROP PROCEDURE IF EXISTS GenerateUsers;
DROP PROCEDURE IF EXISTS GenerateOrders;
