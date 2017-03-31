<?php
$themesettings = $PAGE->theme->settings;
$toggleslideshow = $themesettings->toggleslideshow;
$slideinterval = $themesettings->slideinterval;
$noslides = $themesettings->numberofslides;
$devicetype = \core_useragent::get_device_type(); // In moodlelib.php.
$hideslideshow = false;

if ($noslides) {
    if (($devicetype == "mobile") && $themesettings->hideonphone) {
        $hideslideshow = true;
    } else if (($devicetype == "tablet") && $themesettings->hideontablet) {
        $hideslideshow = true;
    }
}

$show = false;
switch ($toggleslideshow) {
    case 1:
        $show = true;
        break;

    case 2:
        if (!isloggedin()) {
            $show = true;
        }
        break;

    case 3:
        if (isloggedin()) {
            $show = true;
        }
        break;

    default:
        $show = false;
}

if ($show && $noslides) {
    $slide = array();
    for ($i = 1; $i <= $noslides; $i++) {
        $slide[$i] = array(
            'img' => $PAGE->theme->setting_file_url('slide' . $i . 'image',
                'slide' . $i . 'image'),
            'captiontitle' => format_text($themesettings->{'slide' . $i . 'captiontitle'}),
            'caption' => format_text($themesettings->{'slide' . $i . 'caption'}),
            'linktext' => format_text($themesettings->{'slide' . $i . 'linktext'}),
            'url' => format_text($themesettings->{'slide' . $i . 'url'}, FORMAT_HTML),
            'target' => $themesettings->{'slide' . $i . 'target'},
        );
    }

    if (!empty($slide[1]['caption'])) {
        $data = array('data' => array('slideinterval' => $slideinterval));
        $PAGE->requires->js_call_amd('theme_kommit/kommitcarousel', 'init', $data);
        if (!$hideslideshow) {
            ?>

            <div id="kslCarousel-wrapper">
                <div class="container-fluid">
                    <div id="kslCarousel" class="carousel slide">
                        <?php if ($noslides > 1) { ?>
                            <ol class="carousel-indicators">
                                <li data-target="#kslCarousel" data-slide-to="0"
                                    class="active"></li>
                                <?php for ($i = 2; $i <= $noslides; $i++) { ?>
                                    <li data-target="#kslCarousel" data-slide-to="<?php echo $i ?>"></li>
                                <?php } ?>
                            </ol>
                        <?php } ?>
                        <!-- Carousel items -->
                        <div class="carousel-inner">
                            <?php for ($i = 1; $i <= $noslides; $i++) {
                                $active = '';
                                if ($i === 1) {
                                    $active = ' active';
                                } ?>
                                <div class="item<?php echo $active; ?>">
                                    <img src="<?php echo $slide[$i]['img']; ?>" alt=""
                                         class="bgimg">

                                    <div class="carousel-caption">
                                        <h1><?php echo $slide[$i]['captiontitle']; ?></h1>
                                        <p><?php echo $slide[$i]['caption']; ?></p>
                                        <?php if (!empty($slide[$i]['linktext'])) { ?>
                                            <div class="buttons">
                                                <a class="btn"
                                                   href="<?php echo $slide[$i]['url']; ?>"
                                                   target="<?php echo $slide[$i]['target']; ?>">
                                                    <?php echo $slide[$i]['linktext']; ?>
                                                </a>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <!-- Carousel nav -->
                        <?php if ($noslides > 1) { ?>
                            <a class="carousel-control left" href="#kslCarousel"
                               data-slide="prev">&lsaquo;</a>
                            <a class="carousel-control right" href="#kslCarousel"
                               data-slide="next">&rsaquo;</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="hero-unit" style="<?php echo $slide[1]['img']; ?>">
                <div class="container-fluid">
                    <div class="texts">
                        <h1><?php echo $slide[1]['captiontitle']; ?></h1>

                        <div class="lead"><?php echo $slide[1]['caption']; ?></div>
                        <div class="buttons">
                            <a class="btn" href="<?php echo $slide[1]['url']; ?>"
                               target="<?php echo $slide[1]['target']; ?>">
                                <?php echo $slide[1]['linktext']; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
    }
} ?>
