<?php
include "config.php";

$isLogin = $func->getMember('active');
$idMember = $func->getMember('id');
$cmd = (!empty($_POST['cmd'])) ? htmlspecialchars($_POST['cmd']) : '';
$sectorType = (!empty($_POST['sectorType'])) ? htmlspecialchars($_POST['sectorType']) : '';
$id = (!empty($_POST['id'])) ? htmlspecialchars($_POST['id']) : 0;
$color = (!empty($_POST['color'])) ? htmlspecialchars($_POST['color']) : 0;
$size = (!empty($_POST['size'])) ? htmlspecialchars($_POST['size']) : 0;
$quantity = (!empty($_POST['quantity'])) ? htmlspecialchars($_POST['quantity']) : 1;
$groupCode = (!empty($_POST['groupCode'])) ? htmlspecialchars($_POST['groupCode']) : '';
$code = (!empty($_POST['code'])) ? htmlspecialchars($_POST['code']) : '';

/* Check login */
if (empty($isLogin)) {
    $data = array('warning' => 'Vui lòng đăng nhập để tiếp tục');
    echo json_encode($data);
    exit;
}

/* check logic group code or code */
if (!empty($groupCode) || !empty($code)) {
    if (!$cart->existsCode($groupCode, $code)) {
        $data = array('error' => 'Dữ liệu không hợp lệ');
        echo json_encode($data);
        exit;
    }
}

if ($cmd == 'add-cart' && $id > 0) {
    /* Check sector */
    if (empty($sectorType)) {
        $data = array('error' => 'Dữ liệu không hợp lệ');
        echo json_encode($data);
        exit;
    }

    /* Get sector */
    $sector = $defineSectors['types'][$sectorType];

    /* Where logic */
    $whereLogicOwner = $func->getLogicOwner($sector['tables']['shop'], $sector);
    $whereDetail = $whereLogicOwner['where'] . $func->getLogicShop($sector['tables']['shop'], $whereLogicOwner);
    $sqlDetail = "select A.id as id from #_" . $sector['tables']['main'] . " as A where A.id = ? $whereDetail limit 0,1";
    $paramsDetail = array($id);
    $logicRow = $d->rawQueryOne($sqlDetail, $paramsDetail);
    
    /* Check logic */
    if (empty($logicRow)) {
        $data = array('error' => 'Dữ liệu không hợp lệ');
        echo json_encode($data);
        exit;
    }

    /* Add cart */
    if ($cart->addToCart($sector, $quantity, $id, $color, $size)) {
        $data = array('success' => 'Đặt hàng thành công');
    } else {
        $data = array('error' => 'Dữ liệu không hợp lệ');
    }

    echo json_encode($data);
} else if ($cmd == 'update-cart' && $groupCode != '' && $code != '') {
    if(!empty($_SESSION['cart'][$config['website']['sectors']][$groupCode]['data'][$code]) && !empty($quantity)){
        $_SESSION['cart'][$config['website']['sectors']][$groupCode]['data'][$code]['qty']= $quantity;
        $infoProduct = $_SESSION['cart'][$config['website']['sectors']][$groupCode]['data'][$code];
    }
    $proinfo = $cart->getProductInfo($infoProduct['productid'], $infoProduct['prefix']);
    $real_price = $func->formatMoney($proinfo['real_price'] * $quantity);
    $total = $cart->getOrderTotal();
    $totalText = $func->formatMoney($total);
    $data = array('realPrice' => $real_price, 'totalText' => $totalText);
    echo json_encode($data);
} else if ($cmd == 'delete-group-cart' && $groupCode != '') {
    $cart->removeGroup($groupCode);
    $max = (!empty($_SESSION['cart'][$config['website']['sectors']])) ? count($_SESSION['cart'][$config['website']['sectors']]) : 0;
    $total = $cart->getOrderTotal();
    $totalText = $func->formatMoney($total);
    $data = array('max' => $max, 'totalText' => $totalText);
    echo json_encode($data);
} else if ($cmd == 'delete-cart' && $groupCode != '' && $code != '') {
    $emptyGroup = false;
    $cart->removeProduct($groupCode, $code);

    /* check if group cart do not have any item => Delete Group */
    if (empty($_SESSION['cart'][$config['website']['sectors']][$groupCode])) $emptyGroup = true;

    $max = (!empty($_SESSION['cart'][$config['website']['sectors']])) ? count($_SESSION['cart'][$config['website']['sectors']]) : 0;
    $total = $cart->getOrderTotal();
    $totalText = $func->formatMoney($total);
    $data = array('max' => $max, 'emptyGroup' => $emptyGroup, 'totalText' => $totalText);

    echo json_encode($data);
}
