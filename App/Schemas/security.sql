CREATE TABLE IF NOT EXISTS voltphp_users
(
    uuid     VARCHAR(255) PRIMARY KEY,
    login    VARCHAR(255),
    csrf     VARCHAR(255),
    username VARCHAR(255) NOT NULL
);