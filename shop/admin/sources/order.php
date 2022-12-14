<?php
if (!defined('SOURCES')) die("Error");

/* Kiểm tra active product */
if (!$func->hasCart($sector)) {
    $func->transfer("Trang không tồn tại", "index.php", false);
}

/* Cấu hình đường dẫn trả về */
$strUrl = "";
$strUrl .= (isset($_REQUEST['order_status'])) ? "&order_status=" . htmlspecialchars($_REQUEST['order_status']) : "";
$strUrl .= (isset($_REQUEST['order_payment'])) ? "&order_payment=" . htmlspecialchars($_REQUEST['order_payment']) : "";
$strUrl .= (isset($_REQUEST['order_date'])) ? "&order_date=" . htmlspecialchars($_REQUEST['order_date']) : "";
$strUrl .= (isset($_REQUEST['range_price'])) ? "&range_price=" . htmlspecialchars($_REQUEST['range_price']) : "";
$strUrl .= (isset($_REQUEST['id_city'])) ? "&id_city=" . htmlspecialchars($_REQUEST['id_city']) : "";
$strUrl .= (isset($_REQUEST['id_district'])) ? "&id_district=" . htmlspecialchars($_REQUEST['id_district']) : "";
$strUrl .= (isset($_REQUEST['id_wards'])) ? "&id_wards=" . htmlspecialchars($_REQUEST['id_wards']) : "";
$strUrl .= (isset($_REQUEST['keyword'])) ? "&keyword=" . htmlspecialchars($_REQUEST['keyword']) : "";

switch ($act) {
    case "man":
        viewMans();
        $template = "order/man/mans";
        break;
    case "edit":
        editMan();
        $template = "order/man/man_add";
        break;
    case "save":
        saveMan();
        break;
    case "delete":
        deleteMan();
        break;
    default:
        $template = "404";
}

/* View order */
function viewMans()
{
    global $d, $idShop, $prefixSector, $func, $strUrl, $curPage, $items, $paging, $minTotal, $maxTotal, $price_from, $price_to, $allNewOrder, $totalNewOrder, $allConfirmOrder, $totalConfirmOrder, $allDeliveriedOrder, $totalDeliveriedOrder, $allCanceledOrder, $totalCanceledOrder;

    /* Get data */
    $where = "";
    $order_status = (isset($_REQUEST['order_status'])) ? htmlspecialchars($_REQUEST['order_status']) : 0;
    $order_payment = (isset($_REQUEST['order_payment'])) ? htmlspecialchars($_REQUEST['order_payment']) : 0;
    $order_date = (isset($_REQUEST['order_date'])) ? htmlspecialchars($_REQUEST['order_date']) : 0;
    $range_price = (isset($_REQUEST['range_price'])) ? htmlspecialchars($_REQUEST['range_price']) : 0;
    $city = (isset($_REQUEST['id_city'])) ? htmlspecialchars($_REQUEST['id_city']) : 0;
    $district = (isset($_REQUEST['id_district'])) ? htmlspecialchars($_REQUEST['id_district']) : 0;
    $wards = (isset($_REQUEST['id_wards'])) ? htmlspecialchars($_REQUEST['id_wards']) : 0;
    $where =' where id<>0 and id_shop ='.$idShop.' and sector_prefix = ?';
    if ($order_status) $where .= " and order_status=$order_status";
    if ($order_payment) $where .= " and JSON_UNQUOTE(JSON_EXTRACT(order_group_info, '$.payments'))=$order_payment";
    if ($order_date) {
        $order_date = explode("-", $order_date);
        $date_from = trim($order_date[0] . ' 12:00:00 AM');
        $date_to = trim($order_date[1] . ' 11:59:59 PM');
        $date_from = strtotime(str_replace("/", "-", $date_from));
        $date_to = strtotime(str_replace("/", "-", $date_to));
        $where .= " and date_created<=$date_to and date_created>=$date_from";
    }
    if ($range_price) {
        $range_price = explode(";", $range_price);
        $price_from = trim($range_price[0]);
        $price_to = trim($range_price[1]);
        $where .= " and total_price<=$price_to and total_price>=$price_from";
    }


    if ($city) $where .= " and JSON_UNQUOTE(JSON_EXTRACT(order_group_info, '$.city')) = $city";
    if ($district) $where .= " and JSON_UNQUOTE(JSON_EXTRACT(order_group_info,'$.district')) = $district";
    if ($wards) $where .= " and JSON_UNQUOTE(JSON_EXTRACT(order_group_info, '$.wards')) = $wards";

    if (isset($_REQUEST['keyword'])) {
        $keyword = htmlspecialchars($_REQUEST['keyword']);
        $where .= " and (code LIKE '%$keyword%' or JSON_EXTRACT(order_group_info, '$.fullname') LIKE '%$keyword%' or JSON_EXTRACT(order_group_info, '$.email') LIKE '%$keyword%' or JSON_EXTRACT(order_group_info, '$.phone') LIKE '%$keyword%')";
    }

    $perPage = 10;
    $startpoint = ($curPage * $perPage) - $perPage;
    $limit = " limit " . $startpoint . "," . $perPage;
    $sql = "select *, JSON_UNQUOTE(JSON_EXTRACT(order_group_info, '$.fullname')) AS fullname from #_order_group $where order by date_created desc $limit";

    $items = $d->rawQuery($sql, array($prefixSector));
    $sqlNum = "select count(*) as 'num' from #_order_group $where order by date_created desc";
    $count = $d->rawQueryOne($sqlNum, array($prefixSector));
    $total = (!empty($count)) ? $count['num'] : 0;
    $url = "index.php?com=order&act=man" . $strUrl;
    $paging = $func->pagination($total, $perPage, $curPage, $url);

    /* Lấy tổng giá min */
    $minTotal = $d->rawQueryOne("select min(total_price),max(total_price) from #_order_group where id_shop = $idShop and sector_prefix = ?", array($prefixSector));
    if ($minTotal['min(total_price)'] != $minTotal['max(total_price)']) $minTotal = $minTotal['min(total_price)'];
    else $minTotal = 0;

    

    /* Lấy tổng giá max */
    $maxTotal = $d->rawQueryOne("select max(total_price) from #_order_group where id_shop = $idShop and sector_prefix = ?", array($prefixSector));
    if ($maxTotal['max(total_price)']) $maxTotal = $maxTotal['max(total_price)'];
    else $maxTotal = 0;

    /* Lấy đơn hàng - mới đặt */
    $order_count = $d->rawQueryOne("select count(id), sum(total_price) from #_order_group where id_shop = $idShop and sector_prefix = ? and order_status = 1", array($prefixSector));
    $allNewOrder = $order_count['count(id)'];
    $totalNewOrder = $order_count['sum(total_price)'];

    /* Lấy đơn hàng - đã xác nhận */
    $order_count = $d->rawQueryOne("select count(id), sum(total_price) from #_order_group where id_shop = $idShop and sector_prefix = ? and order_status = 2", array($prefixSector));
    $allConfirmOrder = $order_count['count(id)'];
    $totalConfirmOrder = $order_count['sum(total_price)'];

    /* Lấy đơn hàng - đã giao */
    $order_count = $d->rawQueryOne("select count(id), sum(total_price) from #_order_group where id_shop = $idShop and sector_prefix = ? and order_status = 4", array($prefixSector));
    $allDeliveriedOrder = $order_count['count(id)'];
    $totalDeliveriedOrder = $order_count['sum(total_price)'];

    /* Lấy đơn hàng - đã hủy */
    $order_count = $d->rawQueryOne("select count(id), sum(total_price) from #_order_group where id_shop = $idShop and sector_prefix = ? and order_status = 5", array($prefixSector));
    $allCanceledOrder = $order_count['count(id)'];
    $totalCanceledOrder = $order_count['sum(total_price)'];
}

/* Edit order */
function editMan()
{
    global $d, $func, $curPage, $item, $orderDetails;

    $id = (!empty($_GET['id'])) ? htmlspecialchars($_GET['id']) : 0;

    if (empty($id)) {
        $func->transfer("Không nhận được dữ liệu", "index.php?com=order&act=man&p=" . $curPage, false);
    } else {
        $item = $d->rawQueryOne("select *, JSON_UNQUOTE(JSON_EXTRACT(order_group_info, '$.fullname')) AS fullname, JSON_UNQUOTE(JSON_EXTRACT(order_group_info, '$.phone')) as phone, JSON_UNQUOTE(JSON_EXTRACT(order_group_info, '$.email')) AS email,JSON_UNQUOTE(JSON_EXTRACT(order_group_info, '$.address')) AS address from #_order_group where id = ? limit 0,1", array($id));

        if (empty($item)) {
            $func->transfer("Dữ liệu không có thực", "index.php?com=order&act=man&p=" . $curPage, false);
        } else {
            /* Get order detail */
            $orderDetails = json_decode($item['order_group_detail'],true);
        }
    }
}

/* Save order */
function saveMan()
{
    global $d, $func, $flash, $curPage;

    /* Check post */
    if (empty($_POST)) {
        $func->transfer("Không nhận được dữ liệu", "index.php?com=order&act=man&p=" . $curPage, false);
    }

    /* Post dữ liệu */
    $message = '';
    $response = array();
    $id = (!empty($_POST['id'])) ? htmlspecialchars($_POST['id']) : 0;
    $data = (!empty($_POST['data'])) ? $_POST['data'] : null;
    if ($data) {
        foreach ($data as $column => $value) {
            $data[$column] = htmlspecialchars($func->sanitize($value));
        }

        $data['date_updated'] = time();
    }

    /* Valid data */
    if (empty($data['order_status'])) {
        $response['messages'][] = 'Chưa chọn tình trạng đơn hàng';
    }

    if (!empty($response)) {
        /* Errors */
        $response['status'] = 'danger';
        $message = base64_encode(json_encode($response));
        $flash->set('message', $message);

        $func->redirect("index.php?com=order&act=edit&id=" . $id);
    }

    /* Save data */
    if ($id) {
        $d->where('id', $id);
        if ($d->update('order_group', $data)) {
            /* Check order status for order main */
            $func->updateOrderMainStatus($id);

            $func->transfer("Cập nhật dữ liệu thành công", "index.php?com=order&act=man&p=" . $curPage);
        } else {
            $func->transfer("Cập nhật dữ liệu bị lỗi", "index.php?com=order&act=man&p=" . $curPage, false);
        }
    } else {
        $func->transfer("Dữ liệu rỗng", "index.php?com=order&act=man&p=" . $curPage, false);
    }
}

/* Delete order */
function deleteMan()
{
    global $d, $idShop, $prefixSector, $func, $curPage;

    $id = (!empty($_GET['id'])) ? htmlspecialchars($_GET['id']) : 0;

    if ($id) {
        $row = $d->rawQueryOne("select id, id_order from #_order_group where id = ? limit 0,1", array($id));

        if (!empty($row)) {
            /* Delete order main */
            $d->rawQuery("delete from #_order_group where id = ?", array($row['id']));

            /* Sum price order group */
            $orderMain = $d->rawQueryOne("select sum(total_price) from #_order_group where id_order = ?", array($row['id_order']));

            /* Update price order main */
            $dataOrderMain = array();
            $dataOrderMain['total_price'] = $orderMain['sum(total_price)'];
            $d->where('id', $row['id_order']);
            $d->update('order', $dataOrderMain);

            $func->transfer("Xóa dữ liệu thành công", "index.php?com=order&act=man&p=" . $curPage);
        } else {
            $func->transfer("Xóa dữ liệu bị lỗi", "index.php?com=order&act=man&p=" . $curPage, false);
        }
    } else {
        $func->transfer("Không nhận được dữ liệu", "index.php?com=order&act=man&p=" . $curPage, false);
    }
}
