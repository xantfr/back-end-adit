<?php
/**
 * TaskFlow API Routes
 * Base URL: http://localhost/TaskFlow_api/api
 *
 * AUTH (tanpa token)
 *   POST   /auth/register.php          { nama, email, password }
 *   POST   /auth/login.php             { email, password }
 *   POST   /auth/logout.php            (Bearer token)
 *
 * PROFILE (Bearer token)
 *   GET    /profile/get_profile.php
 *   PUT    /profile/update_profile.php { nama?, email?, foto_profil? }
 *   POST   /profile/change_password.php { old_password, new_password }
 *
 * TASK (Bearer token)
 *   GET    /task/index.php             ?status=&priority=&id_category=&search=
 *   POST   /task/index.php             { id_category, judul, deskripsi?, deadline, priority?, status? }
 *   GET    /task/detail.php?id=X
 *   PUT    /task/detail.php?id=X       { field yang diupdate }
 *   DELETE /task/detail.php?id=X
 *
 * CATEGORY (Bearer token)
 *   GET    /category/index.php
 *   POST   /category/index.php         { nama_category, warna, icon? }
 *   PUT    /category/update.php?id=X   { field yang diupdate }
 *   DELETE /category/delete.php?id=X
 *
 * REMINDER (Bearer token)
 *   GET    /reminder/index.php         ?id_task=X (opsional)
 *   POST   /reminder/index.php         { id_task, reminder_time, is_active? }
 *   PUT    /reminder/update.php?id=X   { reminder_time?, is_active? }
 *   DELETE /reminder/delete.php?id=X
 *
 * SUBTASK (Bearer token)
 *   GET    /subtask/index.php?id_task=X
 *   POST   /subtask/index.php?id_task=X  { judul, status? }
 *   PUT    /subtask/update.php?id=X      { judul?, status? }
 *   DELETE /subtask/delete.php?id=X
 */
