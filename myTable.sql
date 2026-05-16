  --
  -- Table structure for table `myTable`
  --

  CREATE TABLE `myTable` (
    `ID` int NOT NULL,
    `A` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `B` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `C` varchar(200) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

  ALTER TABLE `myTable`
    ADD UNIQUE KEY `request_id` (`ID`);

  ALTER TABLE `myTable`
    MODIFY `ID` int NOT NULL AUTO_INCREMENT;
  COMMIT;