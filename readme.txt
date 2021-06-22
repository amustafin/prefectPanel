1)
SELECT users.id,
        CONCAT(users.first_name, ' ', users.last_name) AS 'Full Name',
        books.author,
        GROUP_CONCAT(books.name SEPARATOR ', ') as 'Books List'
        FROM user_books
LEFT JOIN books ON user_books.book_id = books.id
LEFT JOIN users ON user_books.user_id = users.id
WHERE users.age BETWEEN 7 AND 17
GROUP BY users.id, books.author
HAVING COUNT(books.author) = 2

2)
Реализация написана на 99%. Исключение составляет проверка формата bearer token,
поскольку для этого пришлось бы писать реализацию еще одного класса,
который не имеет отношения к модулю и является специфическим для конкретной интеграции

