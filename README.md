I was working on a project and in order to test oauth2 redirection, I ended up with something like this:

**Before**
```
$response = $this->get('integrations/calendars?provider=GOOGLE')
            ->assertStatus(302);

$location = $response->headers->get('location');
$query = parse_url($location, PHP_URL_QUERY);
parse_str($query, $params);

$this->assertEquals(secure_url('integrations/calendars/callback'), $params['redirect_uri']);
$this->assertStringContainsString('https://accounts.google.com/o/oauth2/auth', $location);
$this->assertEquals('https://www.googleapis.com/auth/calendar', $params['scope']);
$this->assertEquals(config('services.google.client_id'), $params['client_id']);
$this->assertEquals('true', $params['include_granted_scopes']);
$this->assertEquals('offline', $params['access_type']);
$this->assertEquals('code', $params['response_type']);
$this->assertTrue(strlen($params['state']) === 40);
$this->assertEquals('consent', $params['prompt']);
```
I think it would be cleaner and much more readable if have something similar to ``AssertableJson`` class.

**After**

```
$response = $this->get('integrations/calendars?provider=GOOGLE')
    ->assertRedirect(function (AssertableUri $uri) {
        $uri
            ->whereQuery('redirect_uri', secure_url('integrations/calendars/callback'))
            ->whereQuery('scope', 'https://www.googleapis.com/auth/calendar')
            ->whereQuery('client_id', config('services.google.client_id'))
            ->whereQuery('include_granted_scopes', 'true')
            ->whereQuery('access_type', 'offline')
            ->whereQuery('response_type', 'code')
            ->whereQuery('prompt', 'consent')
            ->whereQuery('state', function($state) {
                return strlen($state) === 40;
            });

    });
```

We can assert other parts of URI using these methods:

- whereFragment($value)
- whereHost($value)
- wherePass($value)
- wherePath($value)
- wherePort($value)
- whereScheme($value)
- whereUser($value)

IMO It's not practical but we can only check for existence of component using these methods:

- hasFragment()
- hasHost()
- hasPass()
- hasPath()
- hasPort()
- hasScheme()
- hasUser()

### ``etc()``

Like AssertableJson it uses ``Interaction`` trait, this will automatically fail your test when you haven't interacted with at least one of the props in a **URI query string**. Hence  the sequence is fixed in other parts of URI; they don't need to be affected by interacted method and can be asserted using ``assertRedirectContains`` method.



```
$assert = new AssertableUri('https://foo.bar?name=Taylor&id=1');
        $assert->whereQuery('name', 'Taylor')
            ->etc() // If remove this line, this will fail
            ->interacted();
```
