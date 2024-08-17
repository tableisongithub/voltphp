CREATE TABLE IF NOT EXISTS voltphp_users
(
    user_id     BIGINT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(255),
    oauth2      BOOLEAN,
    `key`       VARCHAR(255),
    password    VARCHAR(255),
    csrf        VARCHAR(255),
    last_login  DATETIME,
    permissions BIGINT,
    created_at  DATETIME NOT NULL
);
CREATE TABLE IF NOT EXISTS voltphp_users_apikeys
(
    user_id     BIGINT,
    `key`       VARCHAR(255),
    key_id      BIGINT AUTO_INCREMENT,
    permissions TEXT,
    PRIMARY KEY (user_id, key_id)
);