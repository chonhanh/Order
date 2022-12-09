<form id="form-cart" method="post" action="" enctype="multipart/form-data">
    <?= $flash->getMessages("frontend") ?>
    <?php if (!empty($_SESSION['cart'][$config['website']['sectors']])) { ?>
        <div class="custom-alert alert alert-info alert-dismissible fade show" role="alert"><span>Đơn hàng có thể thay đổi tùy thuộc vào tình trạng sản phẩm/gian hàng hoặc chủ sở hữu của sản phẩm/gian hàng.</span><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        <div class="form-row">
            <div class="left-cart col-lg-12 col-12">
                <p class="title-cart bg-white shadow-sm rounded mb-3 px-3 py-3"><?= giohangcuaban ?></p>
                <div class="lists-cart">
                    <?php foreach ($_SESSION['cart'][$config['website']['sectors']] as $k_group_cart => $v_group_cart) {
                        if (!empty($v_group_cart['data'])) { ?>
                            <div class="group-cart group-cart-<?= $v_group_cart['infos']['code'] ?> bg-white shadow-sm rounded px-3 py-3 mb-3">
                                <div class="group-info-cart border-bottom pb-2 mb-2"><strong class="group-name-cart d-block text-capitalize"><?= $v_group_cart['infos']['name'] ?></strong><?= ($v_group_cart['infos']['type'] == 'shop') ? '<a class="small text-success text-decoration-none pr-3" target="_blank" href="' . $configBaseShop . $v_group_cart['infos']['slug_url'] . '/" title="Xem shop"><i class="fas fa-store mr-1"></i>Xem shop</a>' : '<a class="small text-info text-decoration-none pr-3" href="tel:' . $func->parsePhone($v_group_cart['infos']['phone']) . '" title="Liên hệ"><i class="fas fa-mobile-alt mr-1"></i>Liên hệ</a>' ?><a class="delete-groupcart small text-danger text-decoration-none" href="javascript:void(0)" data-group-code="<?= $v_group_cart['infos']['code'] ?>" title="Xóa đơn này"><i class="far fa-trash-alt mr-1"></i>Xóa đơn này</a></div>
                                <div class="group-list-cart">
                                    <div class="label-cart border-bottom pb-2 mb-3">
                                        <div class="row">
                                            <div class="info-procart col-6">Thông tin</div>
                                            <div class="price-procart text-center col-2"><?= dongia ?></div>
                                            <div class="quantity-procart text-center col-2"><?= soluong ?></div>
                                            <div class="final-price-procart text-center col-2"><?= thanhtien ?></div>
                                        </div>
                                    </div>
                                    <?php foreach ($v_group_cart['data'] as $k_cart => $v_cart) {
                                        $code = $k_cart;
                                        $pid = $v_cart['productid'];
                                        $quantity = $v_cart['qty'];
                                        $color = ($v_cart['color']) ? $cart->getSale($v_cart['color'], 'color') : '';
                                        $size = ($v_cart['size']) ? $cart->getSale($v_cart['size'], 'size') : '';
                                        $proinfo = $cart->getProductInfo($pid, $v_cart['prefix']);
                                        $pro_price = $proinfo['real_price'];
                                        $pro_price_qty = $pro_price * $quantity; ?>
                                        <div class="procart procart-<?= $code ?> mb-3">
                                            <div class="row">
                                                <div class="info-procart col-6">
                                                    <div class="d-flex align-items-start justify-content">
                                                        <div class="image-procart text-center">
                                                            <a class="d-block border rounded text-decoration-none p-1 mb-1" href="<?= $v_cart['type'] ?>/<?= $proinfo[$sluglang] ?>/<?= $proinfo['id'] ?>" target="_blank" title="<?= $proinfo['name' . $lang] ?>"><?= $func->getImage(['sizes' => '60x60x2', 'upload' => UPLOAD_PRODUCT_L, 'image' => $proinfo['photo'], 'alt' => $proinfo['name' . $lang]]) ?></a>
                                                            <a class="delete-procart small text-danger text-decoration-none" href="javascript:void(0)" data-group-code="<?=$k_group_cart?>" data-code="<?=$code ?>" title="Xóa"><i class="far fa-trash-alt mr-1"></i>Xóa</a>
                                                        </div>
                                                        <div class="desc-procart">
                                                            <a class="name-procart text-decoration-none text-primary-hover transition" href="<?= $v_cart['type'] ?>/<?= $proinfo[$sluglang] ?>/<?= $proinfo['id'] ?>" target="_blank" title="<?= $proinfo['name' . $lang] ?>"><?= $proinfo['name' . $lang] ?></a>
                                                            <?php if (!empty($color) || !empty($size)) { ?>
                                                                <ul class="sale-procart list-unstyled mt-1 mb-0">
                                                                    <li><?= (!empty($color)) ? '<strong class="pr-1">Màu sắc:</strong><span>' . $color . '</span>' : '' ?></li>
                                                                    <li><?= (!empty($size)) ? '<strong class="pr-1">Kích cỡ:</strong><span>' . $size . '</span>' : '' ?></li>
                                                                </ul>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="final-price-procart text-center col-2"><strong><?= $func->formatMoney($pro_price) ?></strong></div>
                                                <div class="quantity-procart text-center col-2">
                                                    <div class="quantity-counter-procart quantity-counter-procart-<?= $code ?> d-flex align-items-stretch justify-content-between">
                                                        <span class="counter-procart-minus counter-procart">-</span>
                                                        <input type="number" class="qty-procart" min="1" value="<?= $quantity ?>" data-pid="<?= $pid ?>" data-group-code="<?= $v_group_cart['infos']['code'] ?>" data-code="<?= $code ?>" />
                                                        <span class="counter-procart-plus counter-procart">+</span>
                                                    </div>
                                                </div>
                                                <div class="final-price-procart load-price-<?= $code ?> text-center col-2"><strong class="text-danger"><?= $func->formatMoney($pro_price_qty) ?></strong></div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                    <?php }
                    } ?>
                </div>
            </div>
            <div class="right-cart col-lg-12 col-12">
                <div class="money-procart bg-white shadow-sm rounded mb-3 px-3 py-3">
                    <div class="total-procart d-flex align-items-center justify-content-between">
                        <div class="title-cart mb-0"><?= tongcong ?>:</div>
                        <h5 class="total-price load-price-total text-danger mb-0"><?= $func->formatMoney($cart->getOrderTotal()) ?></h5>
                    </div>
                </div>
                <div class="bg-white shadow-sm rounded mb-3 px-3 pt-3 pb-4">
                    <p class="title-cart"><?= hinhthucthanhtoan ?></p>
                    <div class="information-cart">
                        <?php $flashPayment = $flash->get('payments'); ?>
                        <?php foreach ($payments_info as $key => $value) { ?>
                            <div class="payments-cart custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="payments-<?= $value['id'] ?>" name="dataOrder[payments]" value="<?= $value['id'] ?>" <?= (!empty($flashPayment) && $flashPayment == $value['id']) ? 'checked' : '' ?> required>
                                <label class="payments-label custom-control-label" for="payments-<?= $value['id'] ?>" data-payments="<?= $value['id'] ?>"><?= $value['name' . $lang] ?></label>
                                <div class="payments-info payments-info-<?= $value['id'] ?> transition"><?= str_replace("\n", "<br>", $value['desc' . $lang]) ?></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="sticky-cart">
                    <div class="bg-white shadow-sm rounded mb-3 px-3 pt-3 pb-4">
                        <p class="title-cart"><?= thongtingiaohang ?></p>
                        <div class="information-cart">
                            <div class="form-row mb-3">
                                <div class="input-cart col-md-6">
                                    <input type="text" class="form-control text-sm" id="fullname-cart" name="dataOrder[fullname]" placeholder="<?= hoten ?>" value="<?= (!empty($flash->has('fullname'))) ? $flash->get('fullname') : $func->getMember('fullname') ?>" required />
                                </div>
                                <div class="input-cart col-md-6">
                                    <input type="number" class="form-control text-sm" id="phone-cart" name="dataOrder[phone]" placeholder="<?= sodienthoai ?>" value="<?= (!empty($flash->has('phone'))) ? $flash->get('phone') : $func->getMember('phone') ?>" required />
                                </div>
                            </div>
                            <div class="input-cart mb-3">
                                <input type="email" class="form-control text-sm" id="email-cart" name="dataOrder[email]" placeholder="Email" value="<?= (!empty($flash->has('email'))) ? $flash->get('email') : $func->getMember('email') ?>" required />
                            </div>
                            <div class="input-cart mb-3">
                                <select class="select-city-cart custom-select text-sm" required id="city-cart" name="dataOrder[city]">
                                    <option value=""><?= tinhthanh ?></option>
                                    <?php foreach ($city as $k => $v) { ?>
                                        <option value="<?= $v['id'] ?>"><?= $v['name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="input-cart mb-3">
                                <select class="select-district-cart select-district custom-select text-sm" required id="district-cart" name="dataOrder[district]">
                                    <option value=""><?= quanhuyen ?></option>
                                </select>
                            </div>
                            <div class="input-cart mb-3">
                                <select class="select-wards-cart select-wards custom-select text-sm" required id="wards-cart" name="dataOrder[wards]">
                                    <option value=""><?= phuongxa ?></option>
                                </select>
                            </div>
                            <div class="input-cart">
                                <textarea class="form-control text-sm" id="address-cart" name="dataOrder[address]" placeholder="<?= diachi ?>" /><?= (!empty($flash->has('address'))) ? $flash->get('address') : $func->getMember('address') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-cart btn-danger btn-lg btn-block" name="submit-cart" value="<?= thanhtoan ?>" />
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="empty-cart">
            <div class="title-cart bg-white shadow-sm rounded px-3 py-3"><?= giohangcuaban ?></div>
            <div class="bg-white text-center shadow-sm rounded px-3 py-5">
                <?= $func->getImage(['size-error' => '495x265x2', 'upload' => 'assets/images/', 'image' => 'empty-cart.png', 'alt' => 'Không có sản phẩm nào trong giỏ hàng của bạn.']) ?>
                <div class="mt-5 mb-3">Không có sản phẩm nào trong giỏ hàng của bạn.</div>
                <a class="btn btn-sm btn-warning px-3 py-2" href="" title="Tiếp tục mua sắm">Tiếp tục mua sắm</a>
            </div>
        </div>
    <?php } ?>
</form>