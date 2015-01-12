# TwitterOAuth

Application-only authentication for Twitter (https://dev.twitter.com/oauth/application-only).
You can use this class to retrieve a user’s timeline or do a search.

## Usage

```PHP
$twitterAppOAuth = new Iksi\TwitterOAuth($consumerKey, $consumerSecret);

$arguments = array(
    'trim_user' => true,
    'screen_name' => <screen_name>,
    'include_rts' => true,
    'exclude_replies' => true
);

$results = $twitterOAuth->get('statuses/user_timeline', $arguments);
```
