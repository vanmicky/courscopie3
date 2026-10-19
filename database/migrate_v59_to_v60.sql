-- V60 - Descriptions accessibles des images
SET NAMES utf8mb4;

ALTER TABLE levels ADD COLUMN alt_text VARCHAR(255) DEFAULT NULL AFTER image;
ALTER TABLE subjects ADD COLUMN alt_text VARCHAR(255) DEFAULT NULL AFTER image;
