<?php
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    echo "<link href=../css/style.css rel=stylesheet type=text/css>";
    echo "<div class='error msg'>Untuk mengakses Modul anda harus login.</div>";
} else {
    if ($_SESSION['level'] != 'pemilik') {
        echo "<link href=../css/style.css rel=stylesheet type=text/css>";
        echo "<div class='error msg'>Anda tidak berhak mengakses halaman ini.</div>";
    } else {
        $aksi = "modul/mod_kdtk/aksi_kdtk.php";
        switch ($_GET['act']) {
            default:
                $tampil_kdtk = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT kdtk.*, admin.username, admin.nama_lengkap
                    FROM kdtk
                    LEFT JOIN admin ON kdtk.id_admin = admin.id_admin
                    ORDER BY kdtk.id_kdtk DESC");
?>

                <div class="box box-primary box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">KONTROL USER KASIR (KDTK)</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <a class='btn btn-success btn-flat' href='?module=kdtk&act=tambah'>TAMBAH</a>
                        <br><br>

                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Transaksi</th>
                                    <th>ID Admin</th>
                                    <th>Nama Admin</th>
                                    <th>Status</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($r = mysqli_fetch_array($tampil_kdtk)) {
                                    $nama_admin = $r['nama_lengkap'];
                                    if ($nama_admin == '') {
                                        $nama_admin = $r['username'];
                                    }

                                    echo "<tr>
                                        <td>$no</td>
                                        <td>$r[kd_trkasir]</td>
                                        <td>$r[id_admin]</td>
                                        <td>$nama_admin</td>
                                        <td>$r[stt_kdtk]</td>
                                        <td>
                                            <a href='?module=kdtk&act=edit&id=$r[id_kdtk]' title='EDIT' class='btn btn-warning btn-xs'>EDIT</a>
                                            <a href=javascript:confirmdelete('$aksi?module=kdtk&act=hapus&id=$r[id_kdtk]') title='HAPUS' class='btn btn-danger btn-xs'>HAPUS</a>
                                        </td>
                                    </tr>";
                                    $no++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php
                break;

            case "tambah":
                ?>
                <div class='box box-primary box-solid'>
                    <div class='box-header with-border'>
                        <h3 class='box-title'>TAMBAH KONTROL USER</h3>
                        <div class='box-tools pull-right'>
                            <button class='btn btn-box-tool' data-widget='collapse'><i class='fa fa-minus'></i></button>
                        </div>
                    </div>
                    <div class='box-body'>
                        <form method='POST' action='<?php echo $aksi; ?>?module=kdtk&act=input_kdtk' class='form-horizontal'>
                            <div class='form-group'>
                                <label class='col-sm-2 control-label'>Kode Transaksi</label>
                                <div class='col-sm-5'>
                                    <input type='text' name='kd_trkasir' class='form-control' required='required' autocomplete='off'>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='col-sm-2 control-label'>ID Admin</label>
                                <div class='col-sm-3'>
                                    <input type='number' name='id_admin' class='form-control' required='required' min='1'>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='col-sm-2 control-label'>Status</label>
                                <div class='col-sm-3'>
                                    <select name='stt_kdtk' class='form-control'>
                                        <option value='ON'>ON</option>
                                        <option value='OFF'>OFF</option>
                                    </select>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='col-sm-2 control-label'></label>
                                <div class='col-sm-5'>
                                    <input class='btn btn-primary' type='submit' value='SIMPAN'>
                                    <input class='btn btn-danger' type='button' value='BATAL' onclick='self.history.back()'>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
                break;

            case "edit":
                $edit = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM kdtk WHERE id_kdtk='$_GET[id]'");
                $r = mysqli_fetch_array($edit);
                ?>
                <div class='box box-danger box-solid'>
                    <div class='box-header with-border'>
                        <h3 class='box-title'>UBAH KONTROL USER</h3>
                        <div class='box-tools pull-right'>
                            <button class='btn btn-box-tool' data-widget='collapse'><i class='fa fa-minus'></i></button>
                        </div>
                    </div>
                    <div class='box-body'>
                        <form method='POST' action='<?php echo $aksi; ?>?module=kdtk&act=update_kdtk' class='form-horizontal'>
                            <input type='hidden' name='id_kdtk' value='<?php echo $r['id_kdtk']; ?>'>

                            <div class='form-group'>
                                <label class='col-sm-2 control-label'>Kode Transaksi</label>
                                <div class='col-sm-5'>
                                    <input type='text' name='kd_trkasir' class='form-control' value='<?php echo $r['kd_trkasir']; ?>' required='required' autocomplete='off'>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='col-sm-2 control-label'>ID Admin</label>
                                <div class='col-sm-3'>
                                    <input type='number' name='id_admin' class='form-control' value='<?php echo $r['id_admin']; ?>' required='required' min='1'>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='col-sm-2 control-label'>Status</label>
                                <div class='col-sm-3'>
                                    <select name='stt_kdtk' class='form-control'>
                                        <option value='ON' <?php if ($r['stt_kdtk'] == 'ON') {
                                                                echo "selected";
                                                            } ?>>ON</option>
                                        <option value='OFF' <?php if ($r['stt_kdtk'] == 'OFF') {
                                                                echo "selected";
                                                            } ?>>OFF</option>
                                    </select>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='col-sm-2 control-label'></label>
                                <div class='col-sm-5'>
                                    <input class='btn btn-primary' type='submit' value='SIMPAN'>
                                    <input class='btn btn-danger' type='button' value='BATAL' onclick='self.history.back()'>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
<?php
                break;
        }
    }
}
?>
