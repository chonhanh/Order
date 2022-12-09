<?php
if (!defined('SOURCES')) die("Error");

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
    global $d, $func, $strUrl, $curPage, $items, $paging, $minTotal, $maxTotal, $price_from, $price_to, $allNewOrder, $totalNewOrder, $allTradingOrder, $totalTradingOrder, $allDeliveriedOrder, $totalDeliveriedOrder, $allCanceledOrder, $totalCanceledOrder;

    /* Get data */
    $where = "";
    $order_status = (isset($_REQUEST['order_status'])) ? htmlspecialchars($_REQUEST['order_status']) : 0;
    $order_payment = (isset($_REQUEST['order_payment'])) ? htmlspecialchars($_REQUEST['order_payment']) : 0;
    $order_date = (isset($_REQUEST['order_date'])) ? htmlspecialchars($_REQUEST['order_date']) : 0;
    $range_price = (isset($_REQUEST['range_price'])) ? htmlspecialchars($_REQUEST['range_price']) : 0;
    $city = (isset($_REQUEST['id_city'])) ? htmlspecialchars($_REQUEST['id_city']) : 0;
    $district = (isset($_REQUEST['id_district'])) ? htmlspecialchars($_REQUEST['id_district']) : 0;
    $wards = (isset($_REQUEST['id_wards'])) ? htmlspecialchars($_REQUEST['id_wards']) : 0;
    $where =' where id<>0';
    if ($order_status) $where .= " and order_status=$order_status";
    if ($order_payment) $where .= " and order_payment=$order_payment";
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
    if ($city) $where .= " and JSON_UNQUOTE(JSON_EXTRACT(order_info, '$.city')) = $city";
    if ($district) $where .= " and JSON_UNQUOTE(JSON_EXTRACT(order_info,'$.district')) = $district";
    if ($wards) $where .= " and JSON_UNQUOTE(JSON_EXTRACT(order_info, '$.wards')) = $wards";
    if (isset($_REQUEST['keyword'])) {
        $keyword = htmlspecialchars($_REQUEST['keyword']);
        $where .= " and (code LIKE '%$keyword%' or JSON_EXTRACT(order_info, '$.fullname') LIKE '%$keyword%' or JSON_EXTRACT(order_info, '$.email') LIKE '%$keyword%' or JSON_EXTRACT(order_info, '$.phone') LIKE '%$keyword%')";
    }

    $perPage = 10;
    $startpoint = ($curPage * $perPage) - $perPage;
    $limit = " limit " . $startpoint . "," . $perPage;
    $sql = "select *, JSON_UNQUOTE(JSON_EXTRACT(order_info, '$.fullname')) AS fullname  from #_order $where order by date_created desc $limit";

   

    $items = $d->rawQuery($sql);
    $sqlNum = "select count(*) as 'num' from #_order $where order by date_created desc";
    $count = $d->rawQueryOne($sqlNum);
    $total = (!empty($count)) ? $count['num'] : 0;
    $url = "index.php?com=order&act=man" . $strUrl;
    $paging = $func->pagination($total, $perPage, $curPage, $url);

    /* Lấy tổng giá min */
    $minTotal = $d->rawQueryOne("select min(total_price),max(total_price) from #_order");
    if ($minTotal['min(total_price)'] != $minTotal['max(total_price)']) $minTotal = $minTotal['min(total_price)'];
    else $minTotal = 0;

    /* Lấy tổng giá max */
    $maxTotal = $d->rawQueryOne("select max(total_price) from #_order");
    if ($maxTotal['max(total_price)']) $maxTotal = $maxTotal['max(total_price)'];
    else $maxTotal = 0;

    /* Lấy đơn hàng - mới đặt */
    $order_count = $d->rawQueryOne("select count(id), sum(total_price) from #_order where order_status = 1");
    $allNewOrder = $order_count['count(id)'];
    $totalNewOrder = $order_count['sum(total_price)'];

    /* Lấy đơn hàng - đang giao dịch */
    $order_count = $d->rawQueryOne("select count(id), sum(total_price) from #_order where order_status = 6");
    $allTradingOrder = $order_count['count(id)'];
    $totalTradingOrder = $order_count['sum(total_price)'];

    /* Lấy đơn hàng - đã giao */
    $order_count = $d->rawQueryOne("select count(id), sum(total_price) from #_order where order_status = 4");
    $allDeliveriedOrder = $order_count['count(id)'];
    $totalDeliveriedOrder = $order_count['sum(total_price)'];

    /* Lấy đơn hàng - đã hủy */
    $order_count = $d->rawQueryOne("select count(id), sum(total_price) from #_order where order_status = 5");
    $allCanceledOrder = $order_count['count(id)'];
    $totalCanceledOrder = $order_count['sum(total_price)'];
}

/* Edit order */
function editMan()
{
    global $d, $func, $curPage, $item, $orderGroup;

    $id = (!empty($_GET['id'])) ? htmlspecialchars($_GET['id']) : 0;

    if (empty($id)) {
        $func->transfer("Không nhận được dữ liệu", "index.php?com=order&act=man&p=" . $curPage, false);
    } else {
        $item = $d->rawQueryOne("select *, JSON_UNQUOTE(JSON_EXTRACT(order_info, '$.fullname')) AS fullname, JSON_UNQUOTE(JSON_EXTRACT(order_info, '$.phone')) as phone, JSON_UNQUOTE(JSON_EXTRACT(order_info, '$.email')) AS email,JSON_UNQUOTE(JSON_EXTRACT(order_info, '$.address')) AS address from #_order  where id = ? limit 0,1", array($id));

        if (empty($item)) {
            $func->transfer("Dữ liệu không có thực", "index.php?com=order&act=man&p=" . $curPage, false);
        } else {
            /* Get order group */
            $orderGroup = $d->rawQuery("select A.*, B.namevi as statusName, B.class_order as statusClass from #_order_group as A, #_order_status as B where A.order_status = B.id and A.id_order = ?", array($id));

            /* Get order detail and order group info */
            if (!empty($orderGroup)) {
                foreach ($orderGroup as $k_orderGroup => $v_orderGroup) {
                    /* Get order group info */
                    $groupInfo = array();
                    if (!empty($v_orderGroup['id_shop'])) {
                        $groupInfo = $d->rawQueryOne("select id, name as name, slug_url from #_shop_" . $v_orderGroup['sector_prefix'] . " where id = ?", array($v_orderGroup['id_shop']));
                    } else if (!empty($v_orderGroup['id_member'])) {
                        $groupInfo = $d->rawQueryOne("select id, fullname as name, phone from #_member where id = ?", array($v_orderGroup['id_member']));
                    }

                    $orderGroup[$k_orderGroup]['infos'] = $groupInfo;

                    /* Get order detail */
                    $orderGroup[$k_orderGroup]['detail-lists'] = json_decode($v_orderGroup['order_group_detail'],true);
                   
                }
            }
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
    $action = (!empty($_POST['actionOrder'])) ? htmlspecialchars($_POST['actionOrder']) : '';
    $id = (!empty($_POST['id'])) ? htmlspecialchars($_POST['id']) : 0;
    $data = (!empty($_POST['data'])) ? $_POST['data'] : null;
    if ($data) {
        foreach ($data as $column => $value) {
            $data[$column] = htmlspecialchars($func->sanitize($value));
        }
    }

    /* Progess cancel order */
    if ($action == 'cancel-order') {
        $orderDetail = $d->rawQueryOne("select id, order_status from #_order where id = ? limit 0,1", array($id));

        /* Check data main */
        $data['order_status'] = 5;
        $data['date_updated'] = time();

        /* Valid data */
        if (empty($orderDetail)) {
            $response['messages'][] = 'Đơn hàng không tồn tại';
        } else if ($orderDetail['order_status'] == 6) {
            $response['messages'][] = 'Không thể hủy khi đơn hàng đang trong quá trình giao dịch';
        } else if ($orderDetail['order_status'] == 5) {
            $response['messages'][] = 'Đơn hàng đã bị hủy';
        }

        if (!empty($response)) {
            /* Flash data */
            if (!empty($data)) {
                foreach ($data as $k => $v) {
                    if (!empty($v)) {
                        $flash->set($k, $v);
                    }
                }
            }

            /* Errors */
            $response['status'] = 'danger';
            $message = base64_encode(json_encode($response));
            $flash->set('message', $message);
            $func->redirect("index.php?com=order&act=edit&id=" . $id);
        }
    }

    /* Save data */
    if ($id) {
        $d->where('id', $id);
        if ($d->update('order', $data)) {
            /* Progess cancel order */
            if ($action == 'cancel-order') {
                /* update status for order Group */
                $dataOrderGroup = array();
                $dataOrderGroup['order_status'] = 5;
                $d->where('id_order', $id);
                $d->update('order_group', $dataOrderGroup);

                $func->transfer("Hủy đơn hàng thành công", "index.php?com=order&act=man&p=" . $curPage);
            } else {
                $func->transfer("Cập nhật dữ liệu thành công", "index.php?com=order&act=man&p=" . $curPage);
            }
        } else {
            /* Progess cancel order */
            if ($action == 'cancel-order') {
                $func->transfer("Hủy đơn hàng bị lỗi", "index.php?com=order&act=man&p=" . $curPage, false);
            } else {
                $func->transfer("Cập nhật dữ liệu bị lỗi", "index.php?com=order&act=man&p=" . $curPage, false);
            }
        }
    } else {
        $func->transfer("Dữ liệu rỗng", "index.php?com=order&act=man&p=" . $curPage, false);
    }
}

/* Delete order */
function deleteMan()
{
    global $d, $func, $curPage;

    $id = (!empty($_GET['id'])) ? htmlspecialchars($_GET['id']) : 0;

    if ($id) {
        $row = $d->rawQueryOne("select id from #_order where id = ? limit 0,1", array($id));

        if (!empty($row)) {
            /* Delete order main */
            $d->rawQuery("delete from #_order where id = ?", array($row['id']));

            /* Delete order info */
            $d->rawQuery("delete from #_order_info where id_order = ?", array($row['id']));

            /* Get order group */
            $orderGroup = $d->rawQuery("select id from #_order_group where id_order = ?", array($row['id']));

            /* Delete order group */
            $d->rawQuery("delete from #_order_group where id_order = ?", array($row['id']));

            /* Delete order detail */
            if (!empty($orderGroup)) {
                foreach ($orderGroup as $v_orderGroup) {
                    $d->rawQuery("delete from #_order_detail where id_group = ?", array($v_orderGroup['id']));
                }
            }

            $func->transfer("Xóa dữ liệu thành công", "index.php?com=order&act=man&p=" . $curPage);
        } else {
            $func->transfer("Xóa dữ liệu bị lỗi", "index.php?com=order&act=man&p=" . $curPage, false);
        }
    } else {
        $func->transfer("Không nhận được dữ liệu", "index.php?com=order&act=man&p=" . $curPage, false);
    }
}
