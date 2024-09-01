<?php
    define("SERVERNAME", "localhost");
    define("DB_LOGIN", "root");
    define("DB_PASSWORD", "");
    define("DB_NAME", "Библиотека");

    $connect = new mysqli(SERVERNAME, DB_LOGIN, DB_PASSWORD, DB_NAME);
    $book_id = $_GET['id'];
    $book = $connect->query("SELECT Название, И.Наименование AS Издательство, Количество_страниц, Год_издания, ISBN, УДК, ББК, Описание, Изображение FROM Книга К, Издательство И WHERE К.ID = $book_id AND К.ID_издательства = И.ID;")->fetch_assoc();
    $book['Автор']= $connect->query("SELECT GROUP_CONCAT(CONCAT(LEFT(Имя, 1), '.', LEFT(Отчество, 1), '. ', Фамилия)) AS Авторы FROM Автор JOIN Автор_Книги ON Автор.ID = Автор_Книги.ID_автора WHERE Автор_Книги.ID_книги = $book_id;")->fetch_assoc()['Авторы'];
    $book['Жанр']= $connect->query("SELECT GROUP_CONCAT(LOWER(Наименование) SEPARATOR '; ') AS Жанры FROM Жанр JOIN Жанр_Книги ON Жанр.ID = Жанр_Книги.ID_жанра WHERE Жанр_Книги.ID_книги = $book_id;")->fetch_assoc()['Жанры'];
    
    $result = $connect->query("SELECT CONCAT(П.Имя, ' ', П.Отчество, ' ', LEFT(П.Фамилия, 1), '.') AS Пользователь, DATE_FORMAT(О.Дата_и_время, '%d.%m.%Y %H:%i') AS Дата_и_время FROM Очередь О, Пользователь П WHERE О.ID_книги = $book_id AND О.ID_пользователя = П.ID ORDER BY О.ID;");
    for ($queue=array(); $row = $result->fetch_assoc(); $queue[]= $row);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Библиотека</title>
    <link rel="stylesheet" href="/main.css">
    <link rel="stylesheet" href="book.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header_left">
                <a href="../" class="button">Книги</a>
                <a href="../schedule" class="button">График работы</a>
                <a href="../about" class="button">О библиотеке</a>
            </div>
            <div class="header_center"><span>Центральная городская библиотека<br>им. Н.А. Некрасова</span></div>
            <div class="header_right">
                <a id="phone_number" href="tel:+78612743227">8 (861) 274-32-27</a>
                <span>Краснодар, ул. Красная, 87</span>
            </div>
        </header>
        <main>
            <?php
                echo "<img id=\"book_img\" src=\"../img/".$book['Изображение']."\" alt=\"\">";
            ?>
            <div class="main_container">
                <div class="top_container">
                    <div class="book_info">
                        <?php
                            echo "<p id=\"book_name\">".$book['Название']."</p>";
                            echo "<p>Автор: ".$book['Автор']."</p>";
                            echo "<p>Жанр: ".$book['Жанр']."</p>";
                            echo "<p>Издательство: «".$book['Издательство']."»</p>";
                            echo "<p>Количество страниц: ".$book['Количество_страниц']."</p>";
                            echo "<p>Год издания: ".$book["Год_издания"]."</p>";
                            echo "<p>ISBN: ".$book['ISBN']."</p>";
                            echo "<p>УДК: ".$book['УДК']."</p>";
                            echo "<p>ББК: ".$book['ББК']."</p>";
                        ?>
                    </div>
                    <div class="book_description">
                        <?php
                            echo '<p>'.str_replace("\n", "</p><p>", $book['Описание']).'</p>';
                        ?>
                    </div>
                </div>
                <div class="bottom_container">
                    <table id="instance_table">
                        <tr>
                            <th>№ экземпляра книги</th>
                            <th>Статус</th>
                        </tr>
                        <?php
                            $result = $connect->query("SELECT ID FROM Экземпляр_Книги WHERE ID_книги = $book_id;");
                            for ($instances=array(); $row = $result->fetch_assoc(); $instances[]= $row);
                            foreach ($instances as $k=>$v) {
                                echo "<tr>";
                                echo "<td>".$v['ID']."</td>";
                                $result = $connect->query("SELECT CONCAT(П.Имя, ' ', П.Отчество, ' ', LEFT(П.Фамилия, 1), '.') AS Пользователь, DATE_FORMAT(ВК.Дата_и_время_выдачи, '%d.%m.%Y') AS Дата_и_время FROM Выданная_Книга ВК JOIN Пользователь П ON П.ID = ВК.ID_Пользователя WHERE ВК.ID_экземпляра_книги = ".$v['ID'].";")->fetch_assoc();
                                if ($result !== NULL) {
                                    echo "<td>Читает пользователь ".$result['Пользователь']." с ".$result['Дата_и_время']."</td>";
                                }
                                else {
                                    $result = $connect->query("SELECT CONCAT(П.Имя, ' ', П.Отчество, ' ', LEFT(П.Фамилия, 1), '.') AS Пользователь, ГП.Дата_и_время FROM Готов_К_Получению ГП JOIN Пользователь П ON П.ID = ГП.ID_Пользователя WHERE ГП.ID_экземпляра_книги = ".$v['ID'].";")->fetch_assoc();
                                    if ($result !== NULL) {
                                        echo "<td>Ожидание пользователя ".$result['Пользователь']."</td>";
                                    }
                                    else {
                                        echo "<td>Доступно</td";
                                    }
                                }
                                echo "</tr>";
                            }
                        ?>
                    </table>
                    <table id="queue_table">
                        <tr>
                            <th>Место в очереди</th>
                            <th>Пользователь</th>
                            <th>Дата и время записи</th>
                        </tr>
                        <?php
                            foreach ($queue as $k => $v) {
                                echo "<tr>";
                                echo "<td>".($k+1)."</td>";
                                echo "<td>".$v["Пользователь"]."</td>";
                                echo "<td>".$v["Дата_и_время"]."</td>";
                                echo "</tr>";
                            }
                        ?>
                    </table>
                </div>
            </div>
        </main>
        <footer>
        </footer>
    </div>
</body>
</html>