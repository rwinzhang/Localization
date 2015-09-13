<?php namespace Arcanedev\Localization\Tests;

use Arcanedev\Localization\Entities\LocaleCollection;
use Arcanedev\Localization\Localization;

/**
 * Class     LocalizationTest
 *
 * @package  Arcanedev\Localization\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LocalizationTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Localization::class, localization());
    }

    /**
     * @test
     *
     * @expectedException         \Arcanedev\Localization\Exceptions\UnsupportedLocaleException
     * @expectedExceptionMessage  Laravel default locale [jp] is not in the `supported-locales` array.
     */
    public function it_must_throw_unsupported_locale_exception_on_default_locale()
    {
        app('config')->set('app.locale', 'jp');
        new Localization($this->app);
    }

    /**
     * @test
     * @expectedException         \Arcanedev\Localization\Exceptions\UndefinedSupportedLocalesException
     * @expectedExceptionMessage  Supported locales must be defined.
     */
    public function it_must_throw_undefined_supported_locales_exception()
    {
        app('config')->set('localization.supported-locales', []);
        new Localization($this->app);
    }

    /** @test */
    public function it_can_get_supported_locales()
    {
        $supportedLocales = localization()->getSupportedLocales();

        $this->assertInstanceOf(LocaleCollection::class, $supportedLocales);
        $this->assertFalse($supportedLocales->isEmpty());
        $this->assertCount(count($this->supportedLocales), $supportedLocales);

        foreach($this->supportedLocales as $locale) {
            $this->assertTrue($supportedLocales->has($locale));
        }
    }

    /** @test */
    public function it_can_get_supported_locales_keys()
    {
        $this->assertEquals(
            $this->supportedLocales,
            localization()->getSupportedLocalesKeys()
        );
    }

    /** @test */
    public function it_can_set_locale()
    {
        $this->assertEquals(route('about'), 'http://localhost/about');

        $this->refreshApplication('es');

        $this->assertEquals('es', localization()->setLocale('es'));
        $this->assertEquals('es', localization()->getCurrentLocale());
        $this->assertEquals(route('about'), 'http://localhost/acerca');

        $this->refreshApplication();
        $this->localization = app('arcanedev.localization');

        $this->assertEquals('en', localization()->setLocale('en'));
        $this->assertEquals(route('about'), 'http://localhost/about');

        $this->assertNull(localization()->setLocale('de'));
        $this->assertEquals('en', localization()->getCurrentLocale());
    }

    /** @test */
    public function it_can_get_default_locale()
    {
        $this->assertEquals('en', localization()->getDefaultLocale());

        localization()->setLocale('es');
        $this->refreshApplication('es');

        $this->assertEquals('en', localization()->getDefaultLocale());
    }

    /** @test */
    public function it_can_get_current_locale()
    {
        $this->assertEquals('en', localization()->getCurrentLocale());
        $this->assertNotEquals('es', localization()->getCurrentLocale());
        $this->assertNotEquals('fr', localization()->getCurrentLocale());

        localization()->setLocale('es');
        $this->refreshApplication('es');

        $this->assertNotEquals('en', localization()->getCurrentLocale());
        $this->assertEquals('es', localization()->getCurrentLocale());
        $this->assertNotEquals('fr', localization()->getCurrentLocale());

        localization()->setLocale('fr');
        $this->refreshApplication('fr');

        $this->assertNotEquals('en', localization()->getCurrentLocale());
        $this->assertNotEquals('es', localization()->getCurrentLocale());
        $this->assertEquals('fr', localization()->getCurrentLocale());
    }

    /** @test */
    public function it_can_localize_url()
    {
        $this->assertEquals(
            $this->testUrlOne . localization()->getCurrentLocale(),
            localization()->localizeURL()
        );

        // Missing trailing slash in a URL
        $this->assertEquals(
            $this->testUrlTwo . '/' . localization()->getCurrentLocale(),
            localization()->localizeURL()
        );

        app('config')->set('localization.hide-default-in-url', true);

        // testing hide default locale option
        $this->assertNotEquals(
            $this->testUrlOne . localization()->getDefaultLocale(),
            localization()->localizeURL()
        );
        $this->assertEquals(
            $this->testUrlOne,
            localization()->localizeURL()
        );

        localization()->setLocale('es');

        $this->assertEquals(
            $this->testUrlOne . 'es',
            localization()->localizeURL()
        );
        $this->assertEquals(
            $this->testUrlOne . 'about',
            localization()->localizeURL($this->testUrlOne . 'about', 'en')
        );
        $this->assertNotEquals(
            $this->testUrlOne . 'en/about',
            localization()->localizeURL($this->testUrlOne . 'about', 'en')
        );

        app('config')->set('localization.hide-default-in-url', false);

        $this->assertEquals(
            $this->testUrlOne . 'en/about',
            localization()->localizeURL($this->testUrlOne . 'about', 'en')
        );

        $this->assertNotEquals(
            $this->testUrlOne . 'about',
            localization()->localizeURL($this->testUrlOne . 'about', 'en')
        );
    }

    /** @test */
    public function it_can_get_localized_url()
    {
        $this->assertEquals(
            $this->testUrlOne . 'es/acerca',
            localization()->getLocalizedURL('es', $this->testUrlOne . 'en/about')
        );
        $this->assertEquals(
            $this->testUrlOne . 'es/ver/1',
            localization()->getLocalizedURL('es', $this->testUrlOne . 'view/1')
        );
        $this->assertEquals(
            $this->testUrlOne . 'es/ver/1/proyecto',
            localization()->getLocalizedURL('es', $this->testUrlOne . 'view/1/project')
        );
        $this->assertEquals(
            $this->testUrlOne . 'es/ver/1/proyecto/1',
            localization()->getLocalizedURL('es', $this->testUrlOne . 'view/1/project/1')
        );
        $this->assertEquals(
            $this->testUrlOne . 'en/about',
            localization()->getLocalizedURL('en', $this->testUrlOne . 'about')
        );
        $this->assertEquals(
            $this->testUrlOne . localization()->getCurrentLocale(),
            localization()->getLocalizedURL()
        );

        app('config')->set('localization.hide-default-in-url', true);

        // testing default language hidden
        $this->assertEquals(
            $this->testUrlOne . 'es/acerca',
            localization()->getLocalizedURL('es', $this->testUrlOne . 'about')
        );
        $this->assertEquals(
            $this->testUrlOne . 'about',
            localization()->getLocalizedURL('en', $this->testUrlOne . 'about')
        );
        $this->assertEquals(
            $this->testUrlOne,
            localization()->getLocalizedURL()
        );
        $this->assertNotEquals(
            $this->testUrlOne . localization()->getDefaultLocale(),
            localization()->getLocalizedURL()
        );

        localization()->setLocale('es');

        $this->assertNotEquals(
            $this->testUrlOne,
            localization()->getLocalizedURL()
        );
        $this->assertNotEquals(
            $this->testUrlOne . localization()->getDefaultLocale(),
            localization()->getLocalizedURL()
        );
        $this->assertEquals(
            $this->testUrlOne . localization()->getCurrentLocale(),
            localization()->getLocalizedURL()
        );
        $this->assertEquals(
            $this->testUrlOne . 'es/acerca',
            localization()->getLocalizedURL('es', $this->testUrlOne . 'about')
        );

        localization()->setLocale('en');
        $response = $this->makeCall(
            $this->testUrlOne . 'about',
            ['HTTP_ACCEPT_LANGUAGE' => 'en,es']
        );

        $this->assertResponseOk();
        $this->assertEquals($this->testUrlOne . 'es/acerca', $response->getContent());

        $this->refreshApplication();
        app('config')->set('localization.hide-default-in-url', true);

        $this->assertEquals(
            $this->testUrlOne . 'test',
            localization()->getLocalizedURL('en', $this->testUrlOne . 'test')
        );

        $response = $this->makeCall(
            localization()->getLocalizedURL('en', $this->testUrlOne . 'test'),
            ['HTTP_ACCEPT_LANGUAGE' => 'en,es']
        );

        $this->assertResponseOk();
        $this->assertEquals('Test text', $response->getContent());

        $this->refreshApplication('es');

        $this->assertEquals(
            $this->testUrlOne . 'es/test',
            localization()->getLocalizedURL('es', $this->testUrlOne . 'test')
        );
    }

    /** @test */
    public function it_can_get_url_from_route_name_translated()
    {
        $this->assertEquals(
            $this->testUrlOne . 'es/acerca',
            localization()->getURLFromRouteNameTranslated('es', 'localization::routes.about')
        );
        $this->assertEquals(
            $this->testUrlOne . 'en/about',
            localization()->getURLFromRouteNameTranslated('en', 'localization::routes.about')
        );
        $this->assertEquals(
            $this->testUrlOne . 'en/view/1',
            localization()->getURLFromRouteNameTranslated('en', 'localization::routes.view', [ 'id' => 1 ])
        );

        app('config')->set('localization.hide-default-in-url', true);

        $this->assertEquals(
            $this->testUrlOne . 'about',
            localization()->getURLFromRouteNameTranslated('en', 'localization::routes.about')
        );
        $this->assertEquals(
            $this->testUrlOne . 'es/acerca',
            localization()->getURLFromRouteNameTranslated('es', 'localization::routes.about')
        );
        $this->assertEquals(
            $this->testUrlOne . 'es/ver/1',
            localization()->getURLFromRouteNameTranslated('es', 'localization::routes.view', ['id' => 1])
        );
        $this->assertEquals(
            $this->testUrlOne . 'view/1',
            localization()->getURLFromRouteNameTranslated('en', 'localization::routes.view', ['id' => 1])
        );
        $this->assertNotEquals(
            $this->testUrlOne . 'en/view/1',
            localization()->getURLFromRouteNameTranslated('en', 'localization::routes.view', ['id' => 1])
        );

        app('config')->set('localization.hide-default-in-url', false);

        $this->assertNotEquals(
            $this->testUrlOne . 'view/1',
            localization()->getURLFromRouteNameTranslated('en', 'localization::routes.view', ['id' => 1])
        );
        $this->assertEquals(
            $this->testUrlOne . 'en/view/1',
            localization()->getURLFromRouteNameTranslated('en', 'localization::routes.view', ['id' => 1])
        );
    }

    /** @test */
    public function it_can_get_non_localized_url()
    {
        $this->assertEquals(
            $this->testUrlOne,
            localization()->getNonLocalizedURL($this->testUrlOne . 'en')
        );
        $this->assertEquals(
            $this->testUrlOne,
            localization()->getNonLocalizedURL($this->testUrlOne . 'es')
        );
        $this->assertEquals(
            $this->testUrlOne . 'view/1',
            localization()->getNonLocalizedURL($this->testUrlOne . 'en/view/1')
        );
        $this->assertEquals(
            $this->testUrlOne . 'ver/1',
            localization()->getNonLocalizedURL($this->testUrlOne . 'es/ver/1')
        );
    }

    /** @test */
    public function it_can_get_current_locale_name()
    {
        $locales = [
            'en'    => 'English',
            'es'    => 'Spanish',
            'fr'    => 'French',
        ];

        foreach ($locales as $locale => $name) {
            $this->refreshApplication($locale);

            $this->assertEquals($name, localization()->getCurrentLocaleName());
        }
    }

    /** @test */
    public function it_can_get_current_locale_script()
    {
        foreach ($this->supportedLocales as $locale) {
            localization()->setLocale($locale);
            $this->refreshApplication($locale);

            $this->assertEquals('Latn', localization()->getCurrentLocaleScript());
        }
    }

    /** @test */
    public function it_can_get_current_locale_direction()
    {
        foreach ($this->supportedLocales as $locale) {
            $this->refreshApplication($locale);

            $this->assertEquals('ltr', localization()->getCurrentLocaleDirection());
        }
    }

    /** @test */
    public function it_can_get_current_locale_native()
    {
        $locales = [
            'en'    => 'English',
            'es'    => 'Español',
            'fr'    => 'Français',
        ];

        foreach ($locales as $locale => $name) {
            $this->refreshApplication($locale);

            $this->assertEquals($name, localization()->getCurrentLocaleNative());
        }
    }

    /** @test */
    public function it_can_get_config_repository()
    {
        $config = localization()->config();

        $this->assertInstanceOf(\Illuminate\Config\Repository::class, $config);
        $this->assertEquals(app('config'), $config);
    }

    /** @test */
    public function it_can_create_url_from_uri()
    {
        $this->assertEquals(
            'http://localhost/view/1',
            localization()->createUrlFromUri('/view/1')
        );

        localization()->setLocale('es');
        $this->refreshApplication('es');

        $this->assertEquals(
            'http://localhost/ver/1',
            localization()->createUrlFromUri('/ver/1')
        );
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make a call.
     *
     * @param  string  $uri
     * @param  array   $server
     *
     * @return \Illuminate\Http\Response
     */
    public function makeCall($uri, array $server = [])
    {
        return $this->call('GET', $uri, [], [], [], $server);
    }
}