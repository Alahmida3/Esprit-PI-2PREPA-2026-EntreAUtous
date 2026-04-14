<?php
session_start();

// Vérifier si utilisateur connecté
if (!isset($_SESSION['client'])) {
    header("Location: login.php");
    exit();
}

$client = $_SESSION['client'];
?>

<!-- navbar -->
<div class="navbar-glass navbar navbar-expand-lg px-0 px-lg-4">
  <div class="container-fluid px-lg-0">
    <div class="d-flex align-items-center gap-4">

      <!-- Collapse -->
      <div class="d-block d-lg-none">
        <a class="text-inherit" data-bs-toggle="offcanvas" href="#offcanvasExample">
          ☰
        </a>
      </div>

      <div class="d-none d-lg-block">
        <a class="sidebar-toggle d-flex texttooltip p-3" href="javascript:void(0)">
          ⇦
        </a>
      </div>

    </div>

    <!-- Navbar -->
    <ul class="list-unstyled d-flex align-items-center mb-0 gap-2">

      <!-- Search -->
      <li>
        <button class="btn btn-white" data-bs-toggle="modal" data-bs-target="#searchModal">
          🔍
        </button>
      </li>

      <!-- Notifications -->
      <li>
        <a class="btn-icon btn-ghost btn rounded-circle" data-bs-toggle="offcanvas"
          href="#offcanvasNotification">
          🔔
          <span class="badge bg-danger">2</span>
        </a>
      </li>

      <!-- USER DROPDOWN -->
      <li class="ms-3 dropdown">
        <a href="#" data-bs-toggle="dropdown">
          <img src="assets/images/avatar/avatar-1.jpg"
            class="avatar avatar-sm rounded-circle" />
        </a>

        <div class="dropdown-menu dropdown-menu-end p-0">

          <!-- USER INFO -->
          <div class="d-flex gap-3 align-items-center border-bottom px-4 py-4">
            <img src="assets/images/avatar/avatar-1.jpg"
              class="avatar avatar-md rounded-circle" />
            <div>
              <h4 class="mb-0 fs-5"><?php echo $client['name']; ?></h4>
              <p class="mb-0 text-secondary small"><?php echo $client['email']; ?></p>
            </div>
          </div>

          <!-- MENU -->
          <div class="p-3 d-flex flex-column gap-1">

            <a href="client.php" class="dropdown-item">🏠 Home</a>

            <a href="#" class="dropdown-item">📥 Inbox</a>

            <a href="#" class="dropdown-item">💬 Chat</a>

            <a href="#" class="dropdown-item">⚙️ Settings</a>

          </div>

          <!-- LOGOUT -->
          <div class="border-top px-4 py-3">
            <a href="logout.php" class="text-danger">🚪 Logout</a>
          </div>

        </div>
      </li>

    </ul>
  </div>
</div>

<!-- NOTIFICATIONS -->
<div class="offcanvas offcanvas-end" id="offcanvasNotification">
  <div class="offcanvas-header">
    <h5>Notifications</h5>
    <button class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="p-3">
    <p>🔔 New message</p>
    <p>⚠️ Password expiring</p>
  </div>
</div>

<!-- SEARCH MODAL -->
<div class="modal fade" id="searchModal">
  <div class="modal-dialog">
    <div class="modal-content p-3">
      <input type="search" class="form-control" placeholder="Search...">
    </div>
  </div>
</div>