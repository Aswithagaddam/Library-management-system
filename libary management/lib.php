<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Management</title>
    <link rel="stylesheet" href="lib.css">
</head>
<body>
    <div class="container">
        <h1>Library Management System</h1>
        
        <div class="user-role">
            <label for="role">Role:</label>
            <select id="role" name="role">
                <option value="admin">Admin</option>
                <option value="librarian">Librarian</option>
                <option value="member">Member</option>
            </select>
        </div>
        
        <div class="main-grid">
            <div class="card">
                <h2>Search Books</h2>
                <form class="search-bar" method="GET" action="">
                    <input type="text" name="query" placeholder="Search by title, author, or ISBN">
                    <button type="submit">Search</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Issue Book</h2>
                <form class="issue-book" method="POST" action="">
                    <input type="text" name="book_id" placeholder="Book ID">
                    <input type="text" name="member_id" placeholder="Member ID">
                    <button type="submit">Issue</button>
                </form>
            </div>
            
            <div class="card notifications">
                <h2>Notifications</h2>
                <ul>
                    <li>No new notifications.</li>
                </ul>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Books List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Book ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <tr>
                        <td>1</td>
                        <td>Harry Potter</td>
                        <td>J.K. Rowling</td>
                        <td>Available</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>The Hobbit</td>
                        <td>J.R.R. Tolkien</td>
                        <td>Issued</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
