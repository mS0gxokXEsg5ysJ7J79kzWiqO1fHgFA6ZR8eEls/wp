<?php
/**
 * Functions of Tinection WordPress Theme
 *
 * @package   Tinection
 * @version   1.1.6
 * @date      2015.2.3
 * @author    Zhiyan <chinash2010@gmail.com>
 * @site      Zhiyanblog <www.zhiyanblog.com>
 * @copyright Copyright (c) 2014-2015, Zhiyan
 * @license   http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link      http://www.zhiyanblog.com/tinection.html
**/

/* 评论@父级评论
/* -------------- */
function at_comment_parent($comment_text){
	global $comment;
	$return = '';
	if($comment->comment_parent != 0){
		$return .= '<a class="at_parent_comment_author" href="'.htmlspecialchars( get_comment_link( $comment->comment_parent ) ).'">@'.get_comment_author($comment->comment_parent).'</a>';
	}
	$return .= $comment_text;
	return $return;
}
add_filter('comment_text','at_comment_parent');


function is_long_comment($comment){
    $length = 200;
    if(mb_strlen($comment, 'utf8') > $length) {
        return 1;
    }
    return 0;
}
/* 评论调用函数
/* ------------------- */
function tin_comment($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment;
global $commentcount,$wpdb, $post;
     if(!$commentcount) {
          $cnt = $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_post_ID = $post->ID AND comment_type = '' AND char_length(comment_content)>200 AND comment_approved = '1' AND !comment_parent");
          $page = get_query_var('cpage');
          $cpp=get_option('comments_per_page');
         if (ceil($cnt / $cpp) == 1 || ($page > 1 && $page  == ceil($cnt / $cpp))) {
             $commentcount = $cnt + 1;
         } else {
             $commentcount = $cpp * $page + 1;
         }
     }
?>
<li <?php comment_class(); ?> id="comment-<?php comment_ID();?>"  <?php echo is_long_comment(get_comment_excerpt($comment->comment_ID))?'':'style="visibility: hidden; display: none;"'?>>
	<div id="div-comment-<?php comment_ID() ?>" class="comment-body">
	<?php echo tin_get_avatar( $comment->user_id , '54' , tin_get_avatar_type($comment->user_id) ); ?>
	<span class="floor">
		<?php if(!$parent_id = $comment->comment_parent){
			printf('%1$s%2$s',__('#','tinection'),__(--$commentcount));
		}?>
	</span>
	<?php $add_below = 'div-comment'; ?>
	<div class="comment-main">
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<span style="color:#C00; font-style:inherit; margin-top:5px; line-height:25px;"><?php $cpid = $comment->comment_parent; if($cpid!=0)echo '@'; comment_author_link($cpid) ?><?php _e('您的评论正在等待审核中...','tinection'); ?></span>
			<br />
		<?php endif; ?>
		<?php if ( $comment->comment_approved == '1' ) : ?>

            <?php $length = 150;
                  $comment_ex = get_comment_excerpt($comment->comment_ID);
            if(mb_strlen($comment_ex, 'utf8') > $length) {?>
		    <div id="dir_short_<?php echo $comment->comment_ID;?>">
                <?php echo (mb_substr(get_comment_excerpt($comment->comment_ID), 0, 150, 'utf8').'...'); ?>
                <a href="javascript:$('#dir_short_<?php echo $comment->comment_ID;?>').hide();$('#dir_full_<?php echo $comment->comment_ID;?>').show();void(0);">更多</a>
            </div>

            <div id="dir_full_<?php echo $comment->comment_ID;?>" style="display: none;">
                <?php comment_text(); ?>
                <a href="javascript:$('#dir_full_<?php echo $comment->comment_ID;?>').hide();$('#dir_short_<?php echo $comment->comment_ID;?>').show();void(0);">收起</a>
            </div>
            <?php } else {?>
                <div id="dir_full_<?php echo $comment->comment_ID;?>">
                    <?php comment_text(); ?>
                </div>
            <?php }?>
		<?php endif; ?>
		<div class="comment-author">
			<div class="comment-info">
				<span class="comment_author_link"><?php if($comment->user_id != 0){echo '<a href="'.get_author_posts_url($comment->user_id).'" class="name">'.$comment->comment_author.'</a>';}else{comment_author_link();} ?></span>
				<?php if(ot_get_option('comment_vip')=='on') get_author_class($comment->comment_author_email,$comment->user_id); ?>
				<?php if(ot_get_option('comment_ua')=='on') echo outputbrowser($comment->comment_agent); ?>
				<?php if(ot_get_option('comment_ip')=='on') { ?><span class="comment_author_ip tooltip-trigger" title="<?php echo sprintf(__('来自%1$s','tinection'),convertip(get_comment_author_ip())); ?>"><img class="ip_img" src="<?php echo THEME_URI.'/images/ua/ip.png'; ?>"></span><?php } ?>
				<span class="datetime">
					<?php echo timeago(get_gmt_from_date(get_comment_date('Y-m-d G:i:s'))); ?>
				</span>
				<span class="reply">
					<?php if(is_user_logged_in()){comment_reply_link(array_merge( $args, array('reply_text' => __('回复','tinection'), 'add_below' =>$add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'])));}else{echo '<a rel="nofollow" class="comment-reply-login user-login" href="javascript:">'.__('登录以回复','tinection').'</a>';} ?>
				</span>
				<span class="cmt-vote">

                    <?php if(is_user_logged_in()){ ?>
                        <?php $c_name = 'tin_comment_vote_'.$comment->comment_ID;$cookie = isset($_COOKIE[$c_name])?$_COOKIE[$c_name]:'';?>
                        <i class="fa fa-thumbs-o-up <?php if($cookie==1)echo 'voted'; ?>" title="<?php _e('顶一下','tinection'); ?>" data="<?php echo $comment->comment_ID; ?>" data-type="1" data-num="<?php echo (int)get_comment_meta($comment->comment_ID,'tin_comment_voteyes',true); ?>"><?php echo ' ['.(int)get_comment_meta($comment->comment_ID,'tin_comment_voteyes',true).']'; ?></i>
                    <?php }
                    else
                    {
                        echo '<a rel="nofollow" class="comment-reply-login user-login" href="javascript:">'.__('登录后方可为他/她投上你神圣的一票','tinection').'</a>';
                    } ?>

				</span>
				<?php edit_comment_link(__('编辑','tinection'));?>
			</div>
		</div>
	<div class="clear"></div>
	</div>
</div>

<?php
}
function tin_end_comment() {
		echo '</li>';
}




/* 评论调用函数
/* ------------------- */
function tin_comment_short($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    global $commentcount,$wpdb, $post;
    if(!$commentcount) {
        $cnt = $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_post_ID = $post->ID AND comment_type = '' AND char_length(comment_content)<= 200 comment_approved = '1' AND !comment_parent");
        $page = get_query_var('cpage');
        $cpp=get_option('comments_per_page');
        if (ceil($cnt / $cpp) == 1 || ($page > 1 && $page  == ceil($cnt / $cpp))) {
            $commentcount = $cnt + 1;
        } else {
            $commentcount = $cpp * $page + 1;
        }
    }
    ?>
    <li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>" <?php echo is_long_comment(get_comment_excerpt($comment->comment_ID))?'style="visibility: hidden; display: none;"':''?>>
    <div id="div-comment-<?php comment_ID() ?>" class="comment-body">
        <?php echo tin_get_avatar( $comment->user_id , '54' , tin_get_avatar_type($comment->user_id) ); ?>
        <span class="floor">
		<?php if(!$parent_id = $comment->comment_parent){
            printf('%1$s%2$s',__('#','tinection'),__(--$commentcount));
        }?>
	</span>
        <?php $add_below = 'div-comment'; ?>
        <div class="comment-main">
            <?php if ( $comment->comment_approved == '0' ) : ?>
                <span style="color:#C00; font-style:inherit; margin-top:5px; line-height:25px;"><?php $cpid = $comment->comment_parent; if($cpid!=0)echo '@'; comment_author_link($cpid) ?><?php _e('您的评论正在等待审核中...','tinection'); ?></span>
                <br />
            <?php endif; ?>
            <?php if ( $comment->comment_approved == '1' ) : ?>

                <?php $length = 150;
                $comment_ex = get_comment_excerpt($comment->comment_ID);
                if(mb_strlen($comment_ex, 'utf8') > $length) {?>
                    <div id="dir_short_<?php echo $comment->comment_ID;?>">
                        <?php echo (mb_substr(get_comment_excerpt($comment->comment_ID), 0, 150, 'utf8').'...'); ?>
                        <a href="javascript:$('#dir_short_<?php echo $comment->comment_ID;?>').hide();$('#dir_full_<?php echo $comment->comment_ID;?>').show();void(0);">更多</a>
                    </div>

                    <div id="dir_full_<?php echo $comment->comment_ID;?>" style="display: none;">
                        <?php comment_text(); ?>
                        <a href="javascript:$('#dir_full_<?php echo $comment->comment_ID;?>').hide();$('#dir_short_<?php echo $comment->comment_ID;?>').show();void(0);">收起</a>
                    </div>
                <?php } else {?>
                    <div id="dir_full_<?php echo $comment->comment_ID;?>">
                        <?php comment_text(); ?>
                    </div>
                <?php }?>
            <?php endif; ?>
            <div class="comment-author">
                <div class="comment-info">
                    <span class="comment_author_link"><?php if($comment->user_id != 0){echo '<a href="'.get_author_posts_url($comment->user_id).'" class="name">'.$comment->comment_author.'</a>';}else{comment_author_link();} ?></span>
                    <?php if(ot_get_option('comment_vip')=='on') get_author_class($comment->comment_author_email,$comment->user_id); ?>
                    <?php if(ot_get_option('comment_ua')=='on') echo outputbrowser($comment->comment_agent); ?>
                    <?php if(ot_get_option('comment_ip')=='on') { ?><span class="comment_author_ip tooltip-trigger" title="<?php echo sprintf(__('来自%1$s','tinection'),convertip(get_comment_author_ip())); ?>"><img class="ip_img" src="<?php echo THEME_URI.'/images/ua/ip.png'; ?>"></span><?php } ?>
                    <span class="datetime">
					<?php echo timeago(get_gmt_from_date(get_comment_date('Y-m-d G:i:s'))); ?>
				</span>
				<span class="reply">
					<?php if(is_user_logged_in()){comment_reply_link(array_merge( $args, array('reply_text' => __('回复','tinection'), 'add_below' =>$add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'])));}else{echo '<a rel="nofollow" class="comment-reply-login user-login" href="javascript:">'.__('登录以回复','tinection').'</a>';} ?>
				</span>
				<span class="cmt-vote">

                    <?php if(is_user_logged_in()){ ?>
                        <?php $c_name = 'tin_comment_vote_'.$comment->comment_ID;$cookie = isset($_COOKIE[$c_name])?$_COOKIE[$c_name]:'';?>
                        <i class="fa fa-thumbs-o-up <?php if($cookie==1)echo 'voted'; ?>" title="<?php _e('顶一下','tinection'); ?>" data="<?php echo $comment->comment_ID; ?>" data-type="1" data-num="<?php echo (int)get_comment_meta($comment->comment_ID,'tin_comment_voteyes',true); ?>"><?php echo ' ['.(int)get_comment_meta($comment->comment_ID,'tin_comment_voteyes',true).']'; ?></i>
                    <?php }
                    else
                    {
                        echo '<a rel="nofollow" class="comment-reply-login user-login" href="javascript:">'.__('登录后方可为他/她投上你神圣的一票','tinection').'</a>';
                    } ?>

				</span>
                    <?php edit_comment_link(__('编辑','tinection'));?>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>

<?php
}
function tin_end_comment_short() {
    echo '</li>';
}



function tin_comment_quote($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment;
global $commentcount_quote,$wpdb, $post;
     if(!$commentcount_quote) {
          $cnt = $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_post_ID = $post->ID AND (comment_type = 'trackback' OR comment_type = 'pingback') AND comment_approved = '1' AND !comment_parent");
          $page = get_query_var('cpage');
          $cpp=get_option('comments_per_page');
         if (ceil($cnt / $cpp) == 1 || ($page > 1 && $page  == ceil($cnt / $cpp))) {
             $commentcount_quote = $cnt + 1;
         } else {
             $commentcount_quote = $cpp * $page + 1;
         }
     }
?>
<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
   <div id="div-comment-<?php comment_ID() ?>" class="comment-body">
      <?php $add_below = 'div-comment'; ?>
		<div class="comment-author"><?php $uid = get_user_by_email($comment->comment_author_email)->ID;echo tin_get_avatar($uid, 40, tin_get_avatar_type($uid)); ?>
<div style="float:right">
	<span class="datetime">
 		<?php comment_date('Y-m-d') ?><?php comment_time() ?>
 	</span>
 </div>
 <span class="comment_author_link"><?php if($comment->user_id != 0){echo '<a href="'.get_author_posts_url($comment->user_id).'" class="name">'.$comment->comment_author.'</a>';}else{comment_author_link();} ?></span><span class="comment_author_ip"><?php _e('[ 来自 ','tinection'); ?><span><?php echo convertip(get_comment_author_ip()); ?></span>&nbsp;]
</span>
 </div>
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<span style="color:#C00; font-style:inherit; margin-top:5px; line-height:25px;"><?php $cpid = $comment->comment_parent; if($cpid!=0)echo '@'; comment_author_link($cpid) ?><?php _e('您的评论正在等待审核中...','tinection'); ?></span>
			<br />			
		<?php endif; ?>
		<?php if ( $comment->comment_approved == '1' ) : ?>
		<?php comment_text() ?>
		<?php endif; ?>
        </div>
		<div class="clear"></div>
  
<?php
}

/* 评论框的表情包调用
/* ------------------- */
function custom_smilies_src ($img_src, $img, $siteurl){
    return get_bloginfo('template_directory').'/images/smilies/'.$img;
}
add_filter('smilies_src','custom_smilies_src',1,10);

/* 评论框短代码标签
/* ----------------- */
function wp_comment_quicktag() {
    echo '	<span><a href="javascript:SIMPALED.Editor.strong()" rel="external nofollow"  title="'.__('粗体','tinection').'">'.__('粗体','tinection').'</a></span>
			<span><a href="javascript:SIMPALED.Editor.em()" rel="external nofollow"  title="'.__('斜体','tinection').'">'.__('斜体','tinection').'</a></span>
			<span><a href="javascript:SIMPALED.Editor.underline()" rel="external nofollow"  title="'.__('下划线','tinection').'">'.__('下划线','tinection').'</a></span>
			<span><a href="javascript:SIMPALED.Editor.del()" rel="external nofollow"  title="'.__('删除线','tinection').'">'.__('删除线','tinection').'</a></span>
			<span><a href="javascript:SIMPALED.Editor.ahref()" rel="external nofollow"  title="'.__('链接','tinection').'">'.__('链接','tinection').'</a></span>
			<span><a href="javascript:SIMPALED.Editor.quote()" rel="external nofollow"  title="'.__('引用','tinection').'">'.__('引用','tinection').'</a></span>
	';
}
function private_content($atts, $content = null){
	global $comment;
	$author_email = $comment->comment_author_email;
	$parent_email = get_comment_author_email($comment->comment_parent);
	$user = wp_get_current_user();
	$user_id = $user->ID;
	$user_email = $user->user_email;
	if (current_user_can('create_users') || ($user_email == $parent_email && $user_id != 0) || ($author_id != 0 && $author_email == $user_email)){
		return '' . $content . '';
	}else{
		return __('***隐藏内容仅管理员和父级评论者可见***','tinection');
	}		
}
add_shortcode('private', 'private_content');
add_filter('comment_text', 'do_shortcode'); /* 评论内容添加隐藏功能短代码*/

/* 移除评论HTML标签过滤器，慎启
/* ------------------------------ */
//remove_action('init', 'kses_init');
//remove_action('set_current_user', 'kses_init');

/* 评论数学验证码
/* ---------------- */
function spam_protection_math(){
	//获取两个随机数, 范围0~50
	$num1=rand(0,50);
	$num2=rand(0,50);
	echo "<p id='captcha-field'><input type='text' name='sum' id='captcha' class='math_textfield' value='' size='22' tabindex='4'><label for='math'>".__('验证码','tinection')."</label><span style='color:red; font-family:Microsoft YaHei,arial;'> $num1 + $num2 = ?</span>"
."<input type='hidden' name='num1' value='$num1'>"."<input type='hidden' name='num2' value='$num2'>"."</p>";
}

/* 评论 VIP
/* ------------- */  
function get_author_class($comment_author_email,$user_id){  
	global $wpdb;  
	$author_count = $wpdb->get_var("SELECT COUNT(comment_ID) as author_count FROM $wpdb->comments WHERE comment_author_email = '$comment_author_email' ");  
	$adminEmail = get_option('admin_email');
	$authorEmail = get_the_author_meta('email');
	if(!$comment_author_email) echo '<span class="comment_author_vip tooltip-trigger" title="'.__('评论达人 LV.1','tinection').'"><span class="vip vip1">'.__('评论达人 LV.1','tinection').'</span></span>'; else{
	if($comment_author_email && $user_id && $comment_author_email == $adminEmail){
		echo '<span class="comment_author_vip tooltip-trigger" title="'.__('博主','tinection').'"><span class="vip vip-blogger">'.__('管理员','tinection').'</span></span>';
		}elseif($user_id && $comment_author_email == $authorEmail){
			echo '<span class="comment_author_vip tooltip-trigger" title="'.__('作者','tinection').'"><span class="vip vip-author">'.__('作 者','tinection').'</span></span>';
		}elseif($author_count>=1 && $author_count<3){  
			echo '<span class="comment_author_vip tooltip-trigger" title="'.__('评论达人 LV.1','tinection').'"><span class="vip vip1">'.__('评论达人 LV.1','tinection').'</span></span>';  
		}elseif($author_count>=3 && $author_count<5){   
			echo '<span class="comment_author_vip tooltip-trigger" title="'.__('评论达人 LV.2','tinection').'"><span class="vip vip2">'.__('评论达人 LV.2','tinection').'</span></span>';  
		}elseif($author_count>=5 && $author_count<10){  
			echo '<span class="comment_author_vip tooltip-trigger" title="'.__('评论达人 LV.3','tinection').'"><span class="vip vip3">'.__('评论达人 LV.3','tinection').'</span></span>';   
		}elseif($author_count>=10 && $author_count<20){   
			echo '<span class="comment_author_vip tooltip-trigger" title="'.__('评论达人 LV.4','tinection').'"><span class="vip vip4">'.__('评论达人 LV.4','tinection').'</span></span>';   
		}elseif($author_count>=20 &&$author_count<50){   
			echo '<span class="comment_author_vip tooltip-trigger" title="'.__('评论达人 LV.5','tinection').'"><span class="vip vip5">'.__('评论达人 LV.5','tinection').'</span></span>';   
		}elseif($author_count>=50 && $author_count<100){   
			echo '<span class="comment_author_vip tooltip-trigger" title="'.__('评论达人 LV.6','tinection').'"><span class="vip vip6">'.__('评论达人 LV.6','tinection').'</span></span>';   
		}elseif($author_count>=100)   
			echo '<span class="comment_author_vip tooltip-trigger" title="'.__('评论达人 LV.7','tinection').'"><span class="vip vip7">'.__('评论达人 LV.7','tinection').'</span></span>';   
	}
}

/* 评论过滤
/* --------- */  
function refused_spam_comments( $comment_data ){  
	$pattern = '/[一-龥]/u';  
	$jpattern ='/[ぁ-ん]+|[ァ-ヴ]+/u';
	if(!preg_match($pattern,$comment_data['comment_content'])){  
		err('不能纯英文评论！请输入中文!');
	} 
	if(preg_match($jpattern, $comment_data['comment_content'])){
		err('请输入中文!');
	}
	return( $comment_data );  
}  
if(ot_get_option('span_comments')){
add_filter('preprocess_comment','refused_spam_comments');
}
?>