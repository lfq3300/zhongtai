<?php
header("Content-Type: text/html;charset=utf-8");
$json = '
{"authorizer_info":{"nick_name":"鍗婇〉涔︾","head_img":"http:\/\/wx.qlogo.cn\/mmopen\/Xhv9t0E2UvMDy9KO3QJyOeiboTXyL8Ix53SbkGKWc30RzQFVRiaeYA7Su26yJIs9avgtAmB2M6kwXNC51yItKJ4fP1noMbbCJR\/0","service_type_info":{"id":1},"verify_type_info":{"id":0},"user_name":"gh_a5fed18a7e33","alias":"BYSQ66","qrcode_url":"http:\/\/mmbiz.qpic.cn\/mmbiz_jpg\/X5RgyiaPQbX1cGlV8vlo5hJgUuLAMCcVkVGBqxUjU0ficBb7UU7hRBowoQI3E5zlHkToPoeAeCjjdxoUMLMcCENg\/0","business_info":{"open_pay":0,"open_shake":0,"open_scan":0,"open_card":0,"open_store":0},"idc":1,"principal_name":"骞垮窞鑱氬叴鏂囧寲浼犳挱鏈夐檺鍏徃","signature":"杩欎釜涓栫晫寰堝ぇ,浣犲緢娓哄皬,娌℃湁涓斁澶ч暅,浣犳槸鐪嬩笉娓呬汉蹇冪殑!"},"authorization_info":{"authorizer_appid":"wxf825ca9817d90977","authorizer_refresh_token":"refreshtoken@@@NS5M_RWdpy5JKbtFP_bE68wXVM3j-1gl7P2stVWyy4A","func_info":[{"funcscope_category":{"id":1}},{"funcscope_category":{"id":15}},{"funcscope_category":{"id":4}},{"funcscope_category":{"id":7}},{"funcscope_category":{"id":2}},{"funcscope_category":{"id":3}},{"funcscope_category":{"id":11}},{"funcscope_category":{"id":6}},{"funcscope_category":{"id":5}},{"funcscope_category":{"id":8}},{"funcscope_category":{"id":13}},{"funcscope_category":{"id":9}},{"funcscope_category":{"id":10}},{"funcscope_category":{"id":12}},{"funcscope_category":{"id":22}},{"funcscope_category":{"id":23}},{"funcscope_category":{"id":24},"confirm_info":{"need_confirm":0,"already_confirm":0,"can_confirm":0}},{"funcscope_category":{"id":26}},{"funcscope_category":{"id":27},"confirm_info":{"need_confirm":0,"already_confirm":0,"can_confirm":0}},{"funcscope_category":{"id":33},"confirm_info":{"need_confirm":0,"already_confirm":0,"can_confirm":0}},{"funcscope_category":{"id":35}}]}}';
print_r(json_decode($json,true));