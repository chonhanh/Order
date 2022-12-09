<?php
class Cart
{
    private $d;
    private $func;
    private $cartSectors;

    function __construct($d, $func,$config)
    {
        $this->d = $d;
        $this->func = $func;
        $this->cartSectors = $config['website']['sectors'];
        /* Check logic */
        $this->checkLogic();
    }

    public function checkLogic(){
        if (!empty($_SESSION['cart'][$this->cartSectors])) {
            foreach ($_SESSION['cart'][$this->cartSectors] as $k_group_cart => $v_group_cart) {
                if ($v_group_cart['infos']['type'] == 'shop') {
                    $checkGroup = $this->d->rawQueryOne("select id from #_shop_" . $v_group_cart['infos']['prefix'] . " where id = ? and status = ? and status_user = ? and id_member in (select id from #_member where find_in_set('hienthi',status)) limit 0,1", array($v_group_cart['infos']['id'], 'xetduyet', 'hienthi'));
                } else if ($v_group_cart['infos']['type'] == 'member') {
                    $checkGroup = $this->d->rawQueryOne("select id from #_member where id = ? and find_in_set('hienthi',status)", array($v_group_cart['infos']['id']));
                }
                /* Delete Group if it is UnActive */
                if (empty($checkGroup)) $this->removeGroup($k_group_cart);
                else { /* Delete items in Group member/shop if it is UnActive or items in shop UnActive */ 
                    foreach ($v_group_cart['data'] as $k_cart => $v_cart) {
                        $checkItem = $this->d->rawQueryOne("select id from #_product_" . $v_cart['prefix'] . " where id = ? and status = 'xetduyet' and (status_user = 'hienthi' or find_in_set('hienthi',status_attr))", array($v_cart['productid']));
                        if (empty($checkItem)) $this->removeProduct($k_group_cart, $k_cart);
                    }
                }
            }
        }
    }

    public function existsCode($groupCode = '', $code = '')
    {
        $result = true;

        if ($groupCode != '' || $code != '') {
            if (!empty($groupCode) && !isset($_SESSION['cart'][$this->cartSectors][$groupCode])) {
                $result = false;
            }

            if (!empty($code)) {
                if (empty($_SESSION['cart'][$this->cartSectors][$groupCode]['data'])) {
                    $result = false;
                } else {
                    $count = 0;
                    foreach ($_SESSION['cart'][$this->cartSectors][$groupCode]['data'] as $k_cart => $v_cart) {
                        if ($k_cart != $code) $count++;
                    }
                    if ($count == count($_SESSION['cart'][$this->cartSectors][$groupCode]['data'])) {
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }

    public function getProductInfo($pid = 0, $sector_prefix = '')
    {
        $result = null;

        if (!empty($pid) && !empty($sector_prefix)) {
            $result = $this->d->rawQueryOne("select id, namevi, slugvi, photo, regular_price, real_price from #_product_$sector_prefix where id = ? limit 0,1", array($pid));
        }

        return $result;
    }

    public function getSale($id = 0, $sale = '')
    {
        $str = '';

        if (!empty($id) && !empty($sale)) {
            $row = $this->d->rawQueryOne("select namevi from #_product_$sale where id = ? limit 0,1", array($id));
            $str = $row['namevi'];
        }

        return $str;
    }

    public function removeGroup($groupCode = '')
    {
        unset($_SESSION['cart'][$this->cartSectors][$groupCode]);
    }

    public function removeProduct($groupCode = '', $code = '')
    {
        unset($_SESSION['cart'][$this->cartSectors][$groupCode]['data'][$code]);
        if(empty($_SESSION['cart'][$this->cartSectors][$groupCode]['data'])) $this->removeGroup($groupCode);
    }

    public function getOrderTotal()
    {
        $sum = 0;
        if (!empty($_SESSION['cart'][$this->cartSectors])) {
            foreach ($_SESSION['cart'][$this->cartSectors] as $v_group_cart) {
                if (!empty($v_group_cart['data'])) {
                    foreach ($v_group_cart['data'] as $v_cart) {
                        $q = $v_cart['qty'];
                        $proinfo = $this->getProductInfo($v_cart['productid'], $v_cart['prefix']);
                        $price = $proinfo['real_price'];
                        $sum += ($price * $q);
                    }
                }
            }
        }

        return $sum;
    }

    public function getOrderGroupTotal($groupCode = '')
    {
        $sum = 0;

        if (!empty($_SESSION['cart'][$this->cartSectors]) && $groupCode != '' && !empty($_SESSION['cart'][$this->cartSectors][$groupCode]['data'])) {
            foreach ($_SESSION['cart'][$this->cartSectors][$groupCode]['data'] as $v_cart) {
                $q = $v_cart['qty'];
                $proinfo = $this->getProductInfo($v_cart['productid'], $v_cart['prefix']);
                $price = $proinfo['real_price'];
                $sum += ($price * $q);
            }
        }

        return $sum;
    }

    private function getOwner($sector = array(), $pid = 0)
    {
        $result = $selects = array();

        if (!empty($sector) && !empty($pid)) {
            $row = $this->d->rawQueryOne("select id, id_shop, id_member from #_" . $sector['tables']['main'] . " where id = ? and id_admin = 0 limit 0,1", array($pid));

            if (!empty($row)) {
                if (!empty($row['id_shop'])) {
                    $selects['type'] = 'shop';
                    $selects['cols'] = 'id as id, id_member as id_member, name as name, slug_url as slug_url, photo as photo';
                    $selects['tbl'] = $sector['tables']['shop'];
                    $selects['id'] = $row['id_shop'];
                } else if (!empty($row['id_member'])) {
                    $selects['type'] = 'member';
                    $selects['cols'] = 'id as id, fullname as name, phone as phone';
                    $selects['tbl'] = 'member';
                    $selects['id'] = $row['id_member'];
                }

                /* Get data owner */
                $rows = $this->d->rawQueryOne("select " . $selects['cols'] . " from #_" . $selects['tbl'] . " where id = ? limit 0,1", array($selects['id']));
                
                if (!empty($rows)) {
                    /* Data */
                    $nameConvert = str_replace("-", "", $this->func->changeTitle($rows['name']));
                    $rows['type'] = $selects['type'];

                    /* Result */
                    $result['data'] = $rows;
                    $result['key'] = $selects['type'] .  '-' . $nameConvert . '-' . $rows['id'];

                    if ($selects['type'] == 'shop') {
                        $result['data']['prefix'] = $sector['prefix'];

                        /* Get owner info */
                        if (!empty($rows['id_member'])) {
                            $selectOwner = array();
                            $selectOwner['tbl'] = 'member';
                            $selectOwner['id'] = $rows['id_member'];
                            $selectOwner['type'] = 'member';
                        }

                        /* Get and set owner info for shop */
                        if (!empty($selectOwner)) {
                            $shopOwnerInfo = $this->d->rawQueryOne("select fullname as name from #_" . $selectOwner['tbl'] . " where id = ? limit 0,1", array($selectOwner['id']));
                            if (!empty($shopOwnerInfo)) {
                                $nameOwnerConvert = str_replace("-", "", $this->func->changeTitle($shopOwnerInfo['name']));
                                $result['data']['ownerCode'] = md5($selectOwner['type'] . '-' . $nameOwnerConvert . '-' . $selectOwner['id']);
                            }
                        }
                    } 

                    $result['data']['code'] = md5($result['key']);
                }
            }
        }

        return $result;
    }

    public function addToCart($sector = array(), $q = 1, $pid = 0, $color = 0, $size = 0)
    {
        if (empty($sector) or $pid < 1 or $q < 1) return false;
        $owner = $this->getOwner($sector, $pid);
        if (empty($owner)) return false;
        $ownerKey = md5($owner['key']);
        $code = md5($pid . $color . $size);
        if (!empty($_SESSION['cart'][$this->cartSectors][$ownerKey])) {
            if(!empty($_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code])){
                $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['qty']+=$q;
            }else{
                $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['productid'] = $pid;
                $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['qty'] = $q;
                $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['color'] = $color;
                $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['size'] = $size;
                $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['prefix'] = $sector['prefix'];
                $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['type'] = $sector['type'];
            }
        } else {
            $_SESSION['cart'][$this->cartSectors][$ownerKey] = array();
            $_SESSION['cart'][$this->cartSectors][$ownerKey]['infos'] = $owner['data'];
            $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['productid'] = $pid;
            $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['qty'] = $q;
            $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['color'] = $color;
            $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['size'] = $size;
            $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['prefix'] = $sector['prefix'];
            $_SESSION['cart'][$this->cartSectors][$ownerKey]['data'][$code]['type'] = $sector['type'];
        }

        return true;
    }

}
