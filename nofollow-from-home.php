<?php
/*
Plugin Name: Nofollow from Home
Plugin URI: http://www.sochi-travel.info/articles/wp-nofollow-from-home/
Description: This plug-in adds [rel="nofollow"] to all external links in the posts on the home page only.
Author: Irakliy Sunguryan
Version: 2.0
Author URI: http://www.sochi-travel.info/

Using:
  (1) Upload to plugins location
  (2) Activate

TODO:
  * Test/handle relative URLs
  * Add optional handling of "www"?

Version history:
  * 2.0 - rewrite (2017/01/20)
  * 1.0 - initial version (2007/01/30)
*/

/*
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

function nfh_get_domain_name_from_uri($url){
    // this functions should get "domain" part of URL: string between // and first /
    preg_match("/^([^\/]+)/i", $url, $matches);
    $host = $matches[1];
    return $host;
    //preg_match("/[^\.]+\.[^\.]+$/", $host, $matches);    // this only gets TLD part of domain, ignoring subdomains
    //return $matches[0];
}

function nfh_has_no_rel_nofollow($text)
{
    if ( preg_match("/rel=[\"\']\s*?nofollow\s*?[\"\']/i", $text) )
        return false;
    else
        return true;
}


function nfh_parse_external_links($matches)
{
    $site_server_name = $_SERVER['SERVER_NAME'];
    $nofollow = '';

    if (nfh_get_domain_name_from_uri($matches[3]) != $site_server_name &&   // is external site ...
        nfh_has_no_rel_nofollow( $matches[1] ) &&                           // ... and doesn't have nofollow yet
        nfh_has_no_rel_nofollow( $matches[4] ))
    {
        $nofollow = 'rel="nofollow" ';
    }
    
    return '<a '. $nofollow .'rel="nofollow" href="' . $matches[2] . '//' . $matches[3] . '"' .' '. trim($matches[1]) .' '. trim($matches[4]) . '>' . $matches[5] . '</a>';
}

function nfh_set_nofollow($content)
{
    if (is_front_page() && get_query_var('paged') <= 1)    // if it is a front page (static or blog roll, and is page 1 in case of blogroll
    {
        //                1               2        3          4     5
        $pattern = '/<a (.*?)href=[\"\'](.*?)\/\/(.*?)[\"\'](.*?)>(.*?)<\/a>/i';

        // 1 - attributes between [<a ] and [href=]
        // 2 - anchor URL's schema
        // 3 - URL without schema
        // 4 - attributes between [href="url"] and the end of opening anchor tag
        // 5 - anchor text

        //    |-- 1 --------|      |-2-|  |-- 3 --------------------------------------| |-- 4 ---------| |-- 5 ---------|
        // <a rel='nofollow' href='http://www.sochi-travel.info/posts/interesintg-stuff' target='_blank'>Interesting post</a>

        $content = preg_replace_callback($pattern,'nfh_parse_external_links',$content);
    }

    return $content;
}

// add hook
add_filter('the_content', 'nfh_set_nofollow');

?>
