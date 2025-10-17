// --- Mock Data & State ---
let currentUser = null;
let books = [
  { id: 1, title: "1984", author: "George Orwell", isbn: "9780451524935", available: true, issuedTo: null, dueDate: null },
  { id: 2, title: "The Hobbit", author: "J.R.R. Tolkien", isbn: "9780547928227", available: true, issuedTo: null, dueDate: null },
];
let users = [
  { username: "librarian", password: "lib123", role: "librarian" },
  { username: "student", password: "stu123", role: "student" }
];
let transactions = [];
let notifications = [];

// --- Utility Functions ---
function showNotification(msg, type = "info") {
  const area = document.getElementById("notificationArea") || (() => {
    const div = document.createElement("div");
    div.id = "notificationArea";
    document.body.appendChild(div);
    return div;
  })();
  const alert = document.createElement("div");
  alert.className = `alert alert-${type} alert-dismissible fade show`;
  alert.role = "alert";
  alert.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
  area.appendChild(alert);
  setTimeout(() => alert.remove(), 4000);
}

function formatDate(date) {
  if (!date) return "";
  return new Date(date).toLocaleDateString();
}

function calculateFine(dueDate) {
  const now = new Date();
  const due = new Date(dueDate);
  const diff = Math.ceil((now - due) / (1000 * 60 * 60 * 24));
  return diff > 0 ? diff * 2 : 0; // $2 per day late
}

// --- Navbar ---
function renderNavbar() {
  const navLinks = document.getElementById("navLinks");
  navLinks.innerHTML = "";
  if (currentUser) {
    if (currentUser.role === "librarian") {
      navLinks.innerHTML += `<li class="nav-item"><a class="nav-link" href="#" onclick="renderCatalog()">Catalog</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="renderIssueReturn()">Issue/Return</a></li>`;
    } else {
      navLinks.innerHTML += `<li class="nav-item"><a class="nav-link" href="#" onclick="renderCatalog()">Catalog</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="renderProfile()">Profile</a></li>`;
    }
    navLinks.innerHTML += `<li class="nav-item"><a class="nav-link" href="#" onclick="renderNotifications()">Notifications</a></li>
      <li class="nav-item"><a class="nav-link" href="#" onclick="logout()">Logout (${currentUser.username})</a></li>`;
  } else {
    navLinks.innerHTML = `<li class="nav-item"><a class="nav-link" href="#" onclick="renderLogin()">Login</a></li>`;
  }
}

// --- Login ---
function renderLogin() {
  document.getElementById("mainContainer").innerHTML = `
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow">
          <div class="card-body">
            <h3 class="card-title mb-4">Login</h3>
            <form id="loginForm">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select class="form-select" name="role">
                  <option value="student">Student</option>
                  <option value="librarian">Librarian</option>
                </select>
              </div>
              <button class="btn btn-primary w-100">Login</button>
            </form>
          </div>
        </div>
      </div>
    </div>`;
  document.getElementById("loginForm").onsubmit = function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    const username = fd.get("username");
    const password = fd.get("password");
    const role = fd.get("role");
    const user = users.find(u => u.username === username && u.password === password && u.role === role);
    if (user) {
      currentUser = user;
      showNotification("Login successful!", "success");
      renderNavbar();
      renderCatalog();
    } else {
      showNotification("Invalid credentials!", "danger");
    }
  };
}

// --- Catalog ---
function renderCatalog() {
  let html = `
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Book Catalog</h2>
      ${currentUser.role === "librarian" ? `<button class="btn btn-success" onclick="renderBookForm()">Add Book</button>` : ""}
    </div>
    <div class="mb-3">
      <input class="form-control" id="searchInput" placeholder="Search by title, author, ISBN...">
    </div>
    <div class="row" id="bookList"></div>
  `;
  document.getElementById("mainContainer").innerHTML = html;
  renderBookList(books);

  document.getElementById("searchInput").oninput = function () {
    const q = this.value.toLowerCase();
    const filtered = books.filter(b =>
      b.title.toLowerCase().includes(q) ||
      b.author.toLowerCase().includes(q) ||
      b.isbn.includes(q)
    );
    renderBookList(filtered);
  };
}

function renderBookList(bookArr) {
  const list = document.getElementById("bookList");
  if (!list) return;
  list.innerHTML = "";
  if (bookArr.length === 0) {
    list.innerHTML = `<div class="col-12"><div class="alert alert-warning">No books found.</div></div>`;
    return;
  }
  bookArr.forEach(book => {
    let cardClass = book.available ? "card-available" : "card-unavailable";
    let actions = "";
    if (currentUser.role === "librarian") {
      actions = `
        <button class="btn btn-sm btn-primary me-1" onclick="renderBookForm(${book.id})">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteBook(${book.id})">Delete</button>
      `;
    } else if (currentUser.role === "student" && book.available) {
      actions = `<button class="btn btn-sm btn-success" onclick="requestIssue(${book.id})">Request Issue</button>`;
    }
    list.innerHTML += `
      <div class="col-md-4 mb-3">
        <div class="card shadow-sm ${cardClass}">
          <div class="card-body">
            <h5 class="card-title">${book.title}</h5>
            <h6 class="card-subtitle mb-2 text-muted">${book.author}</h6>
            <p class="card-text">ISBN: ${book.isbn}</p>
            <p class="card-text">Status: <span class="${book.available ? "text-success" : "text-danger"}">${book.available ? "Available" : "Issued"}</span></p>
            ${actions}
          </div>
        </div>
      </div>
    `;
  });
}

// --- Book Form (Add/Edit) ---
function renderBookForm(id = null) {
  let book = { title: "", author: "", isbn: "", available: true };
  let isEdit = false;
  if (id) {
    book = books.find(b => b.id === id);
    isEdit = true;
  }
  document.getElementById("mainContainer").innerHTML = `
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-body">
            <h3 class="card-title mb-4">${isEdit ? "Edit" : "Add"} Book</h3>
            <form id="bookForm">
              <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" name="title" value="${book.title}" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Author</label>
                <input type="text" class="form-control" name="author" value="${book.author}" required>
              </div>
              <div class="mb-3">
                <label class="form-label">ISBN</label>
                <input type="text" class="form-control" name="isbn" value="${book.isbn}" required>
              </div>
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="available" id="availableCheck" ${book.available ? "checked" : ""}>
                <label class="form-check-label" for="availableCheck">Available</label>
              </div>
              <button class="btn btn-primary">${isEdit ? "Update" : "Add"} Book</button>
              <button type="button" class="btn btn-secondary ms-2" onclick="renderCatalog()">Cancel</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  `;
  document.getElementById("bookForm").onsubmit = function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    const newBook = {
      id: isEdit ? book.id : Date.now(),
      title: fd.get("title"),
      author: fd.get("author"),
      isbn: fd.get("isbn"),
      available: fd.get("available") === "on",
      issuedTo: null,
      dueDate: null
    };
    if (isEdit) {
      const idx = books.findIndex(b => b.id === book.id);
      books[idx] = newBook;
      showNotification("Book updated!", "success");
    } else {
      books.push(newBook);
      showNotification("Book added!", "success");
    }
    renderCatalog();
  };
}

function deleteBook(id) {
  if (confirm("Are you sure you want to delete this book?")) {
    books = books.filter(b => b.id !== id);
    showNotification("Book deleted!", "success");
    renderCatalog();
  }
}

// --- Issue/Return System (Librarian) ---
function renderIssueReturn() {
  let html = `
    <h2>Issue/Return Books</h2>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Title</th><th>Author</th><th>ISBN</th><th>Status</th><th>Issued To</th><th>Due Date</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          ${books.map(book => `
            <tr>
              <td>${book.title}</td>
              <td>${book.author}</td>
              <td>${book.isbn}</td>
              <td>${book.available ? '<span class="text-success">Available</span>' : '<span class="text-danger">Issued</span>'}</td>
              <td>${book.issuedTo || '-'}</td>
              <td>${book.dueDate ? formatDate(book.dueDate) : '-'}</td>
              <td>
                ${book.available
                  ? `<button class="btn btn-sm btn-success" onclick="issueBook(${book.id})">Issue</button>`
                  : `<button class="btn btn-sm btn-warning" onclick="returnBook(${book.id})">Return</button>`
                }
              </td>
            </tr>
          `).join("")}
        </tbody>
      </table>
    </div>
    <button class="btn btn-secondary" onclick="renderCatalog()">Back to Catalog</button>
  `;
  document.getElementById("mainContainer").innerHTML = html;
}

function issueBook(id) {
  const book = books.find(b => b.id === id);
  const student = prompt("Enter student username to issue:");
  if (!student || !users.find(u => u.username === student && u.role === "student")) {
    showNotification("Invalid student username!", "danger");
    return;
  }
  if (!book.available) {
    showNotification("Book is already issued!", "danger");
    return;
  }
  book.available = false;
  book.issuedTo = student;
  book.dueDate = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000); // 7 days from now
  transactions.push({ bookId: book.id, user: student, action: "issue", date: new Date(), dueDate: book.dueDate });
  notifications.push({ user: student, message: `Book "${book.title}" issued. Due on ${formatDate(book.dueDate)}.` });
  showNotification("Book issued!", "success");
  renderIssueReturn();
}

function returnBook(id) {
  const book = books.find(b => b.id === id);
  if (book.available) {
    showNotification("Book is not issued!", "danger");
    return;
  }
  const fine = calculateFine(book.dueDate);
  transactions.push({ bookId: book.id, user: book.issuedTo, action: "return", date: new Date(), fine });
  notifications.push({ user: book.issuedTo, message: `Book "${book.title}" returned. Fine: $${fine}` });
  book.available = true;
  book.issuedTo = null;
  book.dueDate = null;
  showNotification(`Book returned! Fine: $${fine}`, "success");
  renderIssueReturn();
}

// --- Student: Request Issue ---
function requestIssue(id) {
  const book = books.find(b => b.id === id);
  if (!book.available) {
    showNotification("Book is not available!", "danger");
    return;
  }
  notifications.push({ user: "librarian", message: `Student "${currentUser.username}" requested to issue "${book.title}".` });
  showNotification("Request sent to librarian!", "info");
}

// --- Profile (Student) ---
function renderProfile() {
  const myTrans = transactions.filter(t => t.user === currentUser.username);
  let html = `
    <h2>My Profile</h2>
    <h4 class="mt-4">My Books</h4>
    <ul class="list-group mb-4">
      ${books.filter(b => b.issuedTo === currentUser.username).map(b => `
        <li class="list-group-item d-flex justify-content-between align-items-center">
          ${b.title} (Due: ${formatDate(b.dueDate)})
          <span class="badge bg-${calculateFine(b.dueDate) > 0 ? "danger" : "success"}">
            Fine: $${calculateFine(b.dueDate)}
          </span>
        </li>
      `).join("") || "<li class='list-group-item'>No books issued.</li>"}
    </ul>
    <h4>Transaction History</h4>
    <ul class="list-group">
      ${myTrans.map(t => `
        <li class="list-group-item">
          ${t.action === "issue" ? "Issued" : "Returned"} "${books.find(b => b.id === t.bookId)?.title || "Unknown"}" on ${formatDate(t.date)}
          ${t.fine ? `- Fine: $${t.fine}` : ""}
        </li>
      `).join("") || "<li class='list-group-item'>No transactions.</li>"}
    </ul>
    <button class="btn btn-secondary mt-3" onclick="renderCatalog()">Back to Catalog</button>
  `;
  document.getElementById("mainContainer").innerHTML = html;
}

// --- Notifications ---
function renderNotifications() {
  let myNotes = notifications.filter(n => n.user === currentUser.username || currentUser.role === "librarian");
  let html = `
    <h2>Notifications</h2>
    <ul class="list-group mb-4">
      ${myNotes.map(n => `<li class="list-group-item">${n.message}</li>`).join("") || "<li class='list-group-item'>No notifications.</li>"}
    </ul>
    <button class="btn btn-secondary" onclick="renderCatalog()">Back to Catalog</button>
  `;
  document.getElementById("mainContainer").innerHTML = html;
}

// --- Logout ---
function logout() {
  currentUser = null;
  renderNavbar();
  renderLogin();
}

// --- Initial Render ---
window.onload = function () {
  renderNavbar();
  renderLogin();
};
