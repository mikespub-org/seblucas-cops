-- SQLite
insert into custom_column_2 values (4, "Tree");
insert into custom_column_2 values (5, "Tree.More");
update custom_column_2 set value="Tree.Tag1" where id=1;
update custom_column_2 set value="Tree.More.Tag2" where id=2;
update custom_column_2 set value="Tree.More.Tag3" where id=3;
