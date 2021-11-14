CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    tool_consumer_instance_guid VARCHAR(255) NOT NULL,
    user_id VARCHAR(255) NOT NULL,
    refresh_token VARCHAR(255),
    expires TIMESTAMP
);