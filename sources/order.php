<?php
if (!defined('SOURCES')) die("Error");

/* Get data */
$isLogin = $func->getMember('active');
$idMember = $func->getMember('id');

/* Check login */
if (empty($isLogin)) {
    $urlLogin = $configBase . 'account/dang-nhap';
    $urlRedirect = $urlLogin . '?back=' . $configBase . 'gio-hang';
    $func->redirect($urlRedirect);
}

/* SEO */
$seo->set('title', $title_crumb);

/* City */
$city = $cache->get("select name, id from #_city where find_in_set('hienthi',status) order by numb,id asc", null, 'result', 7200);

/* Payments */
$payments_info = $cache->get("select A.id as id, A.name$lang as name$lang, B.desc$lang as desc$lang from #_news as A, #_news_content as B where A.id = B.id_parent and type = ? order by A.numb,A.id desc", array('hinh-thuc-thanh-toan'), 'result', 7200);

if (!empty($_POST['submit-cart'])) {
    /* Check order */
    if (empty($_SESSION['owner']['cart'])) {
        $func->transfer("Đơn hàng không hợp lệ. Vui lòng thử lại sau.", $configBase, false);
    }

    /* Data */
    $dataOrder = (!empty($_POST['dataOrder'])) ? $_POST['dataOrder'] : null;

    /* Check data */
    if (!empty($dataOrder)) {
        /* Info */
        $code = strtoupper($func->stringRandom(8));
        $order_date = time();
        $fullname = (!empty($dataOrder['fullname'])) ? htmlspecialchars($dataOrder['fullname']) : '';
        $email = (!empty($dataOrder['email'])) ? htmlspecialchars($dataOrder['email']) : '';
        $phone = (!empty($dataOrder['phone'])) ? htmlspecialchars($dataOrder['phone']) : '';
        var_dump($phone);
        /* Place */
        $city = (!empty($dataOrder['city'])) ? htmlspecialchars($dataOrder['city']) : 0;
        $district = (!empty($dataOrder['district'])) ? htmlspecialchars($dataOrder['district']) : 0;
        $wards = (!empty($dataOrder['wards'])) ? htmlspecialchars($dataOrder['wards']) : 0;
        $city_info = $func->getInfoDetail('id_region, name', "city", $city);
        $region = (!empty($city_info)) ? $city_info['id_region'] : 0;
        $district_text = $func->getInfoDetail('name', "district", $district);
        $wards_text = $func->getInfoDetail('name', "wards", $wards);
        $address = htmlspecialchars($dataOrder['address']) . ', ' . $wards_text['name'] . ', ' . $district_text['name'] . ', ' . $city_info['name'];

        /* Payment */
        $order_payment = (!empty($dataOrder['payments'])) ? htmlspecialchars($dataOrder['payments']) : 0;
        $order_payment_data = $func->getInfoDetail('namevi', 'news', $order_payment);
        $order_payment_text = $order_payment_data['namevi'];

        /* Price */
        $total_price = $cart->getOrderTotal();

        /* Nội dung gửi email cho người đặt */
        $contentOrderBookingUser = '';
    }

    /* Valid data */
    if (empty($dataOrder['payments'])) {
        $response['messages'][] = 'Chưa chọn hình thức thanh toán';
    }

    if (empty($dataOrder['fullname'])) {
        $response['messages'][] = 'Họ tên không được trống';
    }

    if (empty($dataOrder['phone'])) {
        $response['messages'][] = 'Số điện thoại không được trống';
    }

    if (!empty($dataOrder['phone']) && !$func->isPhone($dataOrder['phone'])) {
        $response['messages'][] = 'Số điện thoại không hợp lệ';
    }

    if (empty($region)) {
        $response['messages'][] = 'Vùng/miền không hợp lệ';
    }

    if (empty($dataOrder['city'])) {
        $response['messages'][] = 'Chưa chọn tỉnh/thành phố';
    }

    if (empty($dataOrder['district'])) {
        $response['messages'][] = 'Chưa chọn quận/huyện';
    }

    if (empty($dataOrder['wards'])) {
        $response['messages'][] = 'Chưa chọn phường/xã';
    }


    if (empty($dataOrder['address'])) {
        $response['messages'][] = 'Địa chỉ không được trống';
    }

    if (empty($dataOrder['email'])) {
        $response['messages'][] = 'Email không được trống';
    }

    if (!empty($dataOrder['email']) && !$func->isEmail($dataOrder['email'])) {
        $response['messages'][] = 'Email không hợp lệ';
    }


    if (!empty($response)) {
        /* Flash data */
        if (!empty($dataOrder)) {
            foreach ($dataOrder as $k => $v) {
                if (!empty($v)) {
                    $flash->set($k, $v);
                }
            }
        }

        /* Errors */
        $response['status'] = 'danger';
        $message = base64_encode(json_encode($response));
        $flash->set("message", $message);
        $func->redirect("gio-hang");
    }
    
    /* Data order main */
    $dataOrderMain = array();
    $dataOrderMain['id_user'] = (!empty($idMember)) ? $idMember : 0;
    $dataOrderMain['code'] = $code;
    $dataOrderMain['order_status'] = 1;
    $dataOrderMain['order_payment'] = $order_payment;
    $dataOrderMain['total_price'] = $total_price;
    $dataOrderMain['date_created'] = $order_date;
    $dataOrderMain['numb'] = 1;
    $dataOrderMain['order_detail']=array();
    $dataOrderMain['order_info']=json_encode($dataOrder, JSON_UNESCAPED_UNICODE);

    foreach($_SESSION['cart'][$config['website']['sectors']] as $k_cart_group => $v_cart_group){
       foreach ($v_cart_group['data'] as $k_cart => $v) {
            $pid = $v['productid'];
            $q = (!empty($v['qty'])) ? $v['qty'] : 1;
            $proinfo = $cart->getProductInfo($pid, $v['prefix']);
            $real_price = $proinfo['real_price'];
            $color = $cart->getSale($v['color'], 'color');
            $size = $cart->getSale($v['size'], 'size');

            /* Data order detail */
            $detail = array();
            $detail['id_product'] = $pid;
            $detail['photo'] = $proinfo['photo'];
            $detail['name'] = $proinfo['name' . $lang];
            $detail['color'] = $color;
            $detail['size'] = $size;
            $detail['real_price'] = $real_price;
            $detail['quantity'] = $q;
            $detail['sector_prefix'] = $v['prefix'];
            $dataOrderMain['order_detail'][$k_cart]=$detail;
             
       }
    }

    $dataOrderMain['order_detail']=json_encode($dataOrderMain['order_detail'],JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    $func->dump($dataOrder);
    $func->dump($dataOrderMain);die;
    /* Save order main */
    if (!$d->insert('order', $dataOrderMain)) {
        $func->transfer("Xử lý đơn hàng có vấn đề. Vui lòng thử lại sau.", $configBase . 'gio-hang', false);
    } else {
        $idNewOrder = $d->getLastInsertId();
    }

    /* Send each order information for each owner (shop/personal) */
    foreach ($_SESSION['cart'][$config['website']['sectors']] as $k_group_cart => $v_group_cart) {
        $dataOrderGroup = array();
        $dataOrderGroup['id_order'] = $idNewOrder;
        $dataOrderGroup['id_' . $v_group_cart['infos']['type']] = $v_group_cart['infos']['id'];
        $dataOrderGroup['sector_prefix'] = ($v_group_cart['infos']['type'] == 'shop') ? $v_group_cart['infos']['prefix'] : '';
        $dataOrderGroup['total_price'] = $total_group_price = $cart->getOrderGroupTotal($v_group_cart['infos']['code']);
        $dataOrderGroup['order_status'] = 1;
        $dataOrderGroup['code'] = $code;
        $dataOrderGroup['order_payment'] = $order_payment;
        $dataOrderGroup['date_created'] = $order_date;
        $dataOrderGroup['order_group_info'] =json_encode($dataOrder, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        $dataOrderGroup['numb'] = 1;
        $dataOrderGroup['order_group_detail']=array();
        foreach ($v_group_cart['data'] as $k => $v) {
            $pid = $v['productid'];
            $q = (!empty($v['qty'])) ? $v['qty'] : 1;
            $proinfo = $cart->getProductInfo($pid, $v['prefix']);
            $real_price = $proinfo['real_price'];
            $color = $cart->getSale($v['color'], 'color');
            $size = $cart->getSale($v['size'], 'size');

            /* Data order detail */
            $dataOrderDetail = array();
            $dataOrderDetail['id_product'] = $pid;
            $dataOrderDetail['photo'] = $proinfo['photo'];
            $dataOrderDetail['name'] = $proinfo['name' . $lang];
            $dataOrderDetail['color'] = $color;
            $dataOrderDetail['size'] = $size;
            $dataOrderDetail['real_price'] = $real_price;
            $dataOrderDetail['quantity'] = $q;
            $dataOrderDetail['sector_prefix'] = $v['prefix'];
            $dataOrderGroup['order_group_detail'][$k]=$dataOrderDetail;
        }
        $dataOrderGroup['order_group_detail']=json_encode($dataOrderGroup['order_group_detail'],JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        if (!$d->insert('order_group', $dataOrderGroup)) {
            $d->rawQuery("delete from #_order where id = ?", array($idNewOrder));
            $func->transfer("Xử lý đơn hàng có vấn đề. Vui lòng thử lại sau.", $configBase . 'gio-hang', false);
        }
        /* Check owner */
        $selects = array();
        if ($v_group_cart['infos']['type'] == 'shop') {
            $shopDetail = $d->rawQueryOne("select id_member, id_admin, name, slug_url from #_shop_" . $v_group_cart['infos']['prefix'] . " where id = ? limit 0,1", array($v_group_cart['infos']['id']));

            if (!empty($shopDetail['id_member'])) {
                $selects['tbl'] = 'member';
                $selects['id'] = $shopDetail['id_member'];
            } else if (!empty($shopDetail['id_admin'])) {
                $selects['tbl'] = 'user';
                $selects['id'] = $shopDetail['id_admin'];
            }
        } else if ($v_group_cart['infos']['type'] == 'member') {
            $selects['tbl'] = 'member';
            $selects['id'] = $v_group_cart['infos']['id'];
        }

        /* Get owner */
        $ownerOrder = $d->rawQueryOne("select fullname, email from #_" . $selects['tbl'] . " where id = ? limit 0,1", array($selects['id']));
        /* Prepair data for send email */
        $contentOrderLists = '';
        foreach ($v_group_cart['data'] as $k_cart => $v_cart) {
            $pid = $v_cart['productid'];
            $q = (!empty($v_cart['qty'])) ? $v_cart['qty'] : 1;
            $color = $v_cart['color'];
            $size = $v_cart['size'];
            $proinfo = $cart->getProductInfo($pid, $v_cart['prefix']);
            $text_color = $cart->getSale($color, 'color');
            $text_size = $cart->getSale($size, 'size');
            $text_attr = (!empty($text_color) && !empty($text_size)) ? $text_color . " - " . $text_size : ((!empty($text_color)) ? $text_color : ((!empty($text_size)) ? $text_size : ''));

            /* Variables lists order */
            $orderListsVars = array(
                '{productName}',
                '{productAttr}',
                '{productRealPrice}',
                '{productQuantity}',
                '{productRealTotalPrice}'
            );

            /* Values lists order */
            $orderListsVals = array(
                $proinfo['name' . $lang],
                $text_attr,
                $func->formatMoney($proinfo['real_price']),
                $q,
                $func->formatMoney($proinfo['real_price'] * $q)
            );

            /* Get order lists */
            $contentOrderLists .= str_replace($orderListsVars, $orderListsVals, $emailer->markdown('order/lists', ['productAttr' => $text_attr, 'realPrice' => $proinfo['real_price']]));
        }

        /* Nội dung order */
        if ($v_group_cart['infos']['type'] == 'shop') {
            $ownerType = 'GIAN HÀNG';
            $ownerTitle = $func->textConvert($shopDetail['name'], 'upper');
        } else if ($v_group_cart['infos']['type'] == 'member') {
            $ownerType = 'THÀNH VIÊN';
            $ownerTitle = $func->textConvert($ownerOrder['fullname'], 'upper');
        }

        /* Variables group order */
        $orderGroupVars = array(
            '{orderOwnerType}',
            '{orderOwnerTitle}',
            '{orderShopURL}',
            '{orderContentLists}',
            '{orderGroupTotalPrice}',
        );

        /* Values group order */
        $orderGroupVals = array(
            $ownerType,
            $ownerTitle,
            $configBaseShop . $shopDetail['slug_url'] . '/',
            $contentOrderLists,
            $func->formatMoney($total_group_price)
        );

        /* Get order group */
        $contentOrderGroup = str_replace($orderGroupVars, $orderGroupVals, $emailer->markdown('order/group', ['cartInfoType' => $v_group_cart['infos']['type']]));

        /* Add more info content order group */
        $contentOrderBookingUser .= $contentOrderGroup;

        /* Defaults attributes email */
        $emailDefaultAttrs = $emailer->defaultAttrs();

        /* Variables email */
        $emailVars = array(
            '{emailOrderCode}',
            '{emailOrderInfoFullname}',
            '{emailOrderInfoEmail}',
            '{emailOrderInfoPhone}',
            '{emailOrderInfoAddress}',
            '{emailOrderPayment}',
            '{orderContentGroup}'
        );
        $emailVars = $emailer->addAttrs($emailVars, $emailDefaultAttrs['vars']);

        /* Values email */
        $emailVals = array(
            $code,
            $fullname,
            $email,
            $phone,
            $address,
            $order_payment_text,
            $contentOrderGroup
        );
        $emailVals = $emailer->addAttrs($emailVals, $emailDefaultAttrs['vals']);

        /* Send email for every shop/personal */
        $arrayEmail = array(
            "dataEmail" => array(
                "name" => $ownerOrder['fullname'],
                "email" => $ownerOrder['email']
            )
        );
        $subject = "Thông tin đơn hàng từ " . $setting['name' . $lang];
        $message = str_replace($emailVars, $emailVals, $emailer->markdown('order/owner'));
        $file = '';
        $emailer->send("customer", $arrayEmail, $subject, $message, $file);
    }

    /* Send all order information for customer */
    /* Defaults attributes email */
    $emailDefaultAttrs = $emailer->defaultAttrs();

    /* Variables email */
    $emailVars = array(
        '{emailOrderCode}',
        '{emailOrderInfoFullname}',
        '{emailOrderInfoPhone}',
        '{emailOrderInfoEmail}',
        '{emailOrderInfoAddress}',
        '{emailOrderPayment}',
        '{orderContentBookingUser}',
        '{emailOrderTotalPrice}'
    );
    $emailVars = $emailer->addAttrs($emailVars, $emailDefaultAttrs['vars']);

    /* Values email */
    $emailVals = array(
        $code,
        $fullname,
        $phone,
        $email,
        $address,
        $order_payment_text,
        $contentOrderBookingUser,
        $func->formatMoney($total_price)
    );
    $emailVals = $emailer->addAttrs($emailVals, $emailDefaultAttrs['vals']);

    /* Send email for người đặt */
    $arrayEmail = array(
        "dataEmail" => array(
            "name" => $fullname,
            "email" => $email
        )
    );
    $subject = "Thông tin đơn hàng từ " . $setting['name' . $lang];
    $message = $message = str_replace($emailVars, $emailVals, $emailer->markdown('order/customer'));
    $file = '';
    $emailer->send("customer", $arrayEmail, $subject, $message, $file);

    /* Xóa giỏ hàng */
    unset($_SESSION['cart'][$config['website']['sectors']]);
    $func->transfer("Thông tin đơn hàng đã được gửi thành công.", $configBase);
}
