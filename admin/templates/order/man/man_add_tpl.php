<?php
$linkMan = "index.php?com=order&act=man";
$linkSave = "index.php?com=order&act=save";
?>
<!-- Content Header -->
<section class="content-header text-sm">
    <div class="container-fluid">
        <div class="row">
            <ol class="breadcrumb float-sm-left">
                <li class="breadcrumb-item"><a href="index.php" title="Bảng điều khiển">Bảng điều khiển</a></li>
                <li class="breadcrumb-item"><a href="<?= $linkMan ?>" title="Quản lý đơn hàng">Quản lý đơn hàng</a></li>
                <li class="breadcrumb-item active">Thông tin đơn hàng <span class="text-primary">#<?= $item['code'] ?></span></li>
            </ol>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <form id="form-order" method="post" action="<?= $linkSave ?>" enctype="multipart/form-data">
        <div class="card-footer text-sm sticky-top">
            <?php if (!in_array(@$item['order_status'], array(4, 5))) { ?>
                <button type="button" class="btn btn-sm bg-gradient-primary" name="action-order" value="save-order"><i class="far fa-save mr-2"></i>Lưu</button>
            <?php } ?>
            <?php if (@$item['order_status'] == 1) { ?>
                <button type="button" class="btn btn-sm bg-gradient-warning" name="action-order" value="cancel-order"><i class="fas fa-exclamation-triangle mr-2"></i>Hủy đơn hàng</button>
            <?php } ?>
            <a class="btn btn-sm bg-gradient-danger" href="<?= $linkMan ?>" title="Thoát"><i class="fas fa-sign-out-alt mr-2"></i>Thoát</a>
        </div>

        <?= $flash->getMessages('admin') ?>

        <?php if (@$item['order_status'] == 4) { ?>
            <div class="my-alert alert alert-success">Đơn hàng đã hoàn thành vào lúc <?= date("h:i:s A - d/m/Y", @$item['date_updated']) ?></div>
        <?php } else if (@$item['order_status'] == 5) { ?>
            <div class="my-alert alert alert-danger">Đơn hàng đã bị hủy vào lúc <?= date("h:i:s A - d/m/Y", @$item['date_updated']) ?></div>
            <?php } else {
            if (!empty($orderGroup)) { ?>
                <div class="my-alert alert alert-info">Đơn hàng có thể thay đổi tùy thuộc vào tình trạng sản phẩm/gian hàng hoặc chủ sở hữu của sản phẩm/gian hàng.</div>
            <?php } else { ?>
                <div class="my-alert alert alert-warning">Đơn hàng không tồn tại hoặc đã bị xóa bởi các chủ sở hữu</div>
        <?php }
        } ?>

        <div class="card card-primary card-outline text-sm">
            <div class="card-header">
                <h3 class="card-title">Thông tin chính</h3>
            </div>
            <div class="card-body row">
                <div class="form-group col-md-3 col-sm-4">
                    <label>Mã đơn hàng:</label>
                    <p class="text-primary"><?= @$item['code'] ?></p>
                </div>
                <div class="form-group col-md-3 col-sm-4">
                    <label>Hình thức thanh toán:</label>
                    <?php $order_payment = $func->getInfoDetail('namevi', 'news', @$item['order_payment']); ?>
                    <p class="text-info"><?= $order_payment['namevi'] ?></p>
                </div>
                <div class="form-group col-md-3 col-sm-4">
                    <label>Ngày đặt:</label>
                    <p><?= date("h:i:s A - d/m/Y", @$item['date_created']) ?></p>
                </div>
                <div class="form-group col-md-3 col-sm-4">
                    <label>Họ tên:</label>
                    <p class="font-weight-bold text-uppercase text-success"><?= @$item['fullname'] ?></p>
                </div>
                <div class="form-group col-md-3 col-sm-4">
                    <label>Điện thoại:</label>
                    <p><?= @$item['phone'] ?></p>
                </div>
                <div class="form-group col-md-3 col-sm-4">
                    <label>Email:</label>
                    <p><?= @$item['email'] ?></p>
                </div>
                <div class="form-group col-md-6 col-sm-12">
                    <label>Địa chỉ:</label>
                    <p><?= @$item['address'] ?></p>
                </div>
                <div class="form-group col-12">
                    <label for="notes">Ghi chú:</label>
                    <?php if (in_array(@$item['order_status'], array(4, 5))) { ?>
                        <textarea class="form-control text-sm" rows="5" placeholder="Ghi chú" readonly disabled><?= @$item['notes'] ?></textarea>
                    <?php } else { ?>
                        <textarea class="form-control text-sm" name="data[notes]" id="notes" rows="5" placeholder="Ghi chú"><?= @$item['notes'] ?></textarea>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php if (!empty($orderGroup)) { ?>
            <?php foreach ($orderGroup as $v_orderGroup) { ?>
                <div class="card card-primary card-outline text-sm">
                    <div class="card-header">
                        <div class="float-left">
                            <h3 class="card-title text-sm float-none">Đơn hàng thuộc: <strong><?= (!empty($v_orderGroup['id_shop'])) ? '<span class="text-danger">Gian hàng</span>' : '<span class="text-danger">Thành viên</span>' ?> - <?= $v_orderGroup['infos']['name'] ?></strong> (Tình trạng: <strong class="<?= $v_orderGroup['statusClass'] ?>"><?= $v_orderGroup['statusName'] ?></strong>)</h3>
                            <div class="order-group-info mt-1">
                                <?php if (!empty($v_orderGroup['id_shop'])) { ?>
                                    <a class="text-success text-decoration-none" target="_blank" href="<?= $configBaseShop . $v_orderGroup['infos']['slug_url'] ?>/" title="Xem shop"><i class="fas fa-store mr-2"></i>Xem shop</a>
                                <?php } else { ?>
                                    <a class="text-info text-decoration-none" href="tel:<?= $func->parsePhone($v_orderGroup['infos']['phone']) ?>" title="Liên hệ"><i class="fas fa-mobile-alt mr-2"></i>Liên hệ</a>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <?php if (!empty($v_orderGroup['notes'])) { ?>
                            <div class="bg-light border-bottom p-3"><label class="mb-1">Ghi chú:</label>
                                <div><?= nl2br($func->decodeHtmlChars($v_orderGroup['notes'])) ?></div>
                            </div>
                        <?php } ?>
                        <?php if (!empty($v_orderGroup['detail-lists'])) { ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="align-middle text-center" width="10%">STT</th>
                                        <th class="align-middle" style="width:45%">Thông tin</th>
                                        <th class="align-middle text-center">Đơn giá</th>
                                        <th class="align-middle text-center">Số lượng</th>
                                        <th class="align-middle text-right">Tạm tính</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0; foreach ($v_orderGroup['detail-lists'] as $k_orderDetail => $v_orderDetail) {$i++; ?>
                                        <tr>
                                            <td class="align-middle text-center"><?= $i ?></td>
                                            <td class="align-middle">
                                                <div class="row">
                                                    <div class="col-2">
                                                        <div class="bg-white border rounded p-1"><img onerror="src='assets/images/noimage.png'" src="<?= THUMBS ?>/<?= $config['order']['thumb'] ?>/<?= UPLOAD_PRODUCT_L . $v_orderDetail['photo'] ?>" alt="<?= $v_orderDetail['name'] ?>"></div>
                                                    </div>
                                                    <div class="col-10">
                                                        <p class="text-primary mb-1"><?= $v_orderDetail['name'] ?></p>
                                                        <?= (!empty($v_orderDetail['color']) && !empty($v_orderDetail['size'])) ? 'Màu sắc: <strong>' . $v_orderDetail['color'] . "</strong> - Kích cỡ: <strong>" . $v_orderDetail['size'] . '</strong>' : ((!empty($v_orderDetail['color'])) ? 'Màu sắc: <strong>' . $v_orderDetail['color'] . '</strong>' : ((!empty($v_orderDetail['size'])) ? 'Kích cỡ: <strong>' . $v_orderDetail['size'] . '</strong>' : '')); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <strong class="text-dark"><?= $func->formatMoney($v_orderDetail['real_price']) ?></strong>
                                            </td>
                                            <td class="align-middle text-center"><?= $v_orderDetail['quantity'] ?></td>
                                            <td class="align-middle text-right">
                                                <strong class="text-danger"><?= $func->formatMoney($v_orderDetail['real_price'] * $v_orderDetail['quantity']) ?></strong>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-center"><strong>Tổng cộng:</strong></td>
                                        <td class="text-right"><strong class="text-danger"><?= $func->formatMoney($v_orderGroup['total_price']) ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <div class="bg-white rounded shadow-sm mb-3 text-right px-4 py-3">
                <big><strong><span class="text-dark pr-2">Tổng giá trị đơn hàng:</span><span class="text-danger"><?= $func->formatMoney($item['total_price']) ?></span></strong></big>
            </div>
        <?php } ?>

        <div class="card-footer text-sm">
            <?php if (!in_array(@$item['order_status'], array(4, 5))) { ?>
                <button type="button" class="btn btn-sm bg-gradient-primary" name="action-order" value="save-order"><i class="far fa-save mr-2"></i>Lưu</button>
            <?php } ?>
            <?php if (@$item['order_status'] == 1) { ?>
                <button type="button" class="btn btn-sm bg-gradient-warning" name="action-order" value="cancel-order"><i class="fas fa-exclamation-triangle mr-2"></i>Hủy đơn hàng</button>
            <?php } ?>
            <a class="btn btn-sm bg-gradient-danger" href="<?= $linkMan ?>" title="Thoát"><i class="fas fa-sign-out-alt mr-2"></i>Thoát</a>
            <input type="hidden" name="actionOrder" value="">
            <input type="hidden" name="id" value="<?= (isset($item['id']) && $item['id'] > 0) ? $item['id'] : '' ?>">
        </div>
    </form>
</section>