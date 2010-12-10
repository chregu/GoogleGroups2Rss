<?php
// A class to login to Google Groups, save a session cookie
// and update Latitude with a given location.
//
// Original code by:
//
// Nat Friedman <nat@nat.org>
// Jack Catchpoole <jack@catchpoole.com>
// Andy Blyler <ajb@blyler.cc>
//
// MIT license.
//             

class googleLogin
{
        private $cookieFile; // Where we store the Google session cookie
        private $lastURL;    // The previous URL as visited by curl
    
        public function __construct()
        {
                $this->cookieFile = sys_get_temp_dir() . "/google-cookie.txt";
        }
    

    
        // Login to google and save the cookie in $cookieFile
        public function login($username, $password)
        {
                /* obtain needed cookies from the mobile latitude site */
                $html = $this->curlGet("https://groups.google.com/a/liip.ch");
        
                /* obtain login form and cookies */
                $html = 
$this->curlGet("https://www.google.com/a/liip.ch/ServiceLogin2?continue=https%3A%2F%2Fgroups.google.com%2Fa%2Fliip.ch&cd=US&continue2=https%3A%2F%2Fgroups.google.com%2Fa%2Fliip.ch&continue1=https%3A%2F%2Fgroups.google.com%2Fa%2Fliip.ch&hl=en&service=groups2&passive=true&ul=1", 
$this->lastURL);
        
                /* parse out the hidden fields */
                preg_match_all('!hidden.*?name=["\'](.*?)["\'].*?value=["\'](.*?)["\']!ms', $html, $hidden);
        
                /* build post data */
                $post_data = '';
                for($i = 0; $i < count($hidden[1]); $i++)
                {
                        $post_data .= $hidden[1][$i] . '=' . urlencode($hidden[2][$i]) . '&';
                }
        
                $post_data .= "signIn=Sign+in&PersistentCookie=yes";
                $post_data .= "&Email=$username";
                $post_data .= "&Passwd=$password";
        
                /* execute the login */
                $html = $this->curlPost("https://www.google.com/a/liip.ch/LoginAction2?service=groups2", $post_data, $this->lastURL);
        
                /* verify the login was successful */
                /*if (strpos ($html, "Sign in") != FALSE)
                {
                        unlink($this->cookieFile);
                        return false;
                }*/
        
                /* reset the permissions of the cookie file */
                chmod($this->cookieFile, 0600);
                return true;
        }
    
        public function haveCookie()
        {
                return file_exists($this->cookieFile);
        }
    
        public function curlGet($url, $referer = null, $headers = null)
        {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_1_2 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7D11 Safari/528.16");
                if(!is_null($referer)) curl_setopt($ch, CURLOPT_REFERER, $referer);
                if(!is_null($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
//              curl_setopt($ch, CURLOPT_HEADER, true);
                // curl_setopt($ch, CURLOPT_VERBOSE, true);
        
                $html = curl_exec($ch);
        
                if (curl_errno($ch) != 0)
                {
                        die("\nError during GET of '$url': " . curl_error($ch) . "\n");
                }
        
                $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        
                return $html;
        }
    
        public function curlPost($url, $post_vars = null, $referer = null, $headers = null)
        {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_1_2 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7D11 Safari/528.16");
                if(!is_null($referer)) curl_setopt($ch, CURLOPT_REFERER, $referer);
                curl_setopt($ch, CURLOPT_POST, true);
                if(!is_null($post_vars)) curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vars);
                if(!is_null($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
                curl_setopt($ch, CURLOPT_HEADER, true);
                // curl_setopt($ch, CURLOPT_VERBOSE, true);
        
                $html = curl_exec($ch);
        
                if (curl_errno($ch) != 0)
                {
                        die("\nError during POST of '$url': " . curl_error($ch) . "\n");
                }
        
                $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        
                return $html;
        }
}
