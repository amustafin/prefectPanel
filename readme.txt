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
 
