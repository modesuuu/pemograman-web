create table mata_kuliah(`kode` INT AUTO_INCREMENT PRIMARY KEY, `Nama` VARCHAR(50) NOT NULL, `Semester` INT(2) NOT NULL, `Sks` INT(2) NOT NULL );
Query OK, 0 rows affected, 2 warnings (0.04 sec)

INSERT INTO `mata_kuliah`(`Nama`, `Semester`, `Sks`) VALUES ('Jarkom', '2', '2');
Query OK, 1 row affected (0.06 sec)

select * from mata_kuliah;
+------+--------+----------+-----+
| kode | Nama   | Semester | Sks |
+------+--------+----------+-----+
|    1 | Jarkom |        2 |   2 |
+------+--------+----------+-----+
1 row in set (0.00 sec)