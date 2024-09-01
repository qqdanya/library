<?php
    define("SERVERNAME", "localhost");
    define("DB_LOGIN", "root");
    define("DB_PASSWORD", "");
    define("DB_NAME", "Библиотека");

    $connect = new mysqli(SERVERNAME, DB_LOGIN, DB_PASSWORD, DB_NAME);
    $sql = "SELECT ID_книги, SUM(Количество_выдач) AS Количество_выдач, Название, Изображение, И.Наименование AS Издательство FROM Экземпляр_Книги ЭК, Книга К, Издательство И WHERE К.ID = ID_книги AND К.ID_издательства = И.ID GROUP BY ID_книги ORDER BY Количество_выдач DESC, ID_книги ASC;";
    $result = $connect->query($sql);
    for ($book=array(); $row = $result->fetch_assoc(); $book[]= $row);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Библиотека</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="catalog.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header_left">
                <a href="/" class="button">Книги</a>
                <a href="/schedule" class="button">График работы</a>
                <a href="/about" class="button">О библиотеке</a>
            </div>
            <div class="header_center"><span>Центральная городская библиотека<br>им. Н.А. Некрасова</span></div>
            <div class="header_right">
                <a id="phone_number" href="tel:+78612743227">8 (861) 274-32-27</a>
                <span>Краснодар, ул. Красная, 87</span>
            </div>
        </header>
        <main>
            <?php
                foreach ($book as $k=>$v) {
                    $book_id = $v['ID_книги'];
                    $authors = $connect->query("SELECT GROUP_CONCAT(CONCAT(LEFT(Имя, 1), '.', LEFT(Отчество, 1), '. ', Фамилия)) AS Авторы FROM Автор JOIN Автор_Книги ON Автор.ID = Автор_Книги.ID_автора WHERE Автор_Книги.ID_книги = $book_id;")->fetch_assoc()['Авторы'];
                    $genre = $connect->query("SELECT GROUP_CONCAT(LOWER(Наименование) SEPARATOR '; ') AS Жанры FROM Жанр JOIN Жанр_Книги ON Жанр.ID = Жанр_Книги.ID_жанра WHERE Жанр_Книги.ID_книги = $book_id;")->fetch_assoc()['Жанры'];
                    
                    echo "<div class=\"book\">";
                    echo "<a href=\"book?id=$book_id\">";
                    echo "<img src=\"img/".$v['Изображение']."\"></a>";
                    echo "<span id=\"book_name\">".$v['Название']."</span>";
                    echo "<span id=\"author\">$authors</span>";
                    echo "<span id=\"genre\">Жанр: $genre</span>";
                    echo "<span id=\"pub\">Издательство: «".$v['Издательство']."»</span>";
                    echo "<span id=\"issues\">Количество выдач: ".$v['Количество_выдач']."</span>";
                    echo "</div>";
                }
                $connect->close();
            ?>
        </main>
        <footer>
        </footer>
    </div>
</body>
</html>