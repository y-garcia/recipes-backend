USE recipes_dev;

DELIMITER $$

CREATE
    FUNCTION `BIN_TO_UUID`(_bin BINARY(16), swap_flag TINYINT(1)) RETURNS VARCHAR(36) CHARSET utf8
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
    IF _bin IS NULL
    THEN
        RETURN NULL;
    ELSE
        RETURN
            LCASE(CONCAT_WS('-',
                            HEX(SUBSTR(_bin, 5, 4)),
                            HEX(SUBSTR(_bin, 3, 2)),
                            HEX(SUBSTR(_bin, 1, 2)),
                            HEX(SUBSTR(_bin, 9, 2)),
                            HEX(SUBSTR(_bin, 11))));
    END IF;
END$$

CREATE
    FUNCTION `UUID_TO_BIN`(_uuid BINARY(36), swap_flag TINYINT(1)) RETURNS BINARY(16)
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
    IF _uuid IS NULL
    THEN
        RETURN NULL;
    ELSE
        RETURN
            UNHEX(CONCAT(
                    SUBSTR(_uuid, 15, 4),
                    SUBSTR(_uuid, 10, 4),
                    SUBSTR(_uuid, 1, 8),
                    SUBSTR(_uuid, 20, 4),
                    SUBSTR(_uuid, 25)));
    END IF;
END$$

DELIMITER ;
