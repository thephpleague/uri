---
layout: homepage
---

<header>
    <div class="inner-content">
      <a href="http://thephpleague.com/" class="league">
          Presented by The League of Extraordinary Packages
      </a>
      <h1>{{ site.data.project.title }}</h1>
      <h2>{{ site.data.project.tagline }}</h2>
      <p class="composer"><span>$ composer require league/uri</span></p>
    </div>
</header>

<main>

  <div class="example">
    <div class="inner-content">
      <h1>Usage</h1>
<div class="highlighter-rouge"><pre class="highlight"><code><span class="cp">&lt;?php</span>

<span class="k">use</span> <span class="nx">League\Uri\Modifiers\MergeQuery</span><span class="p">;</span>
<span class="k">use</span> <span class="nx">League\Uri\Schemes\Http</span> <span class="k">as</span> <span class="nx">HttpUri</span><span class="p">;</span>

<span class="nv">$base_uri</span> <span class="o">=</span> <span class="s2">"http://www.example.com?fo.o=toto#~typo"</span><span class="p">;</span>
<span class="nv">$query_to_merge</span> <span class="o">=</span> <span class="s1">'fo.o=bar&amp;taz='</span><span class="p">;</span>

<span class="nv">$uri</span> <span class="o">=</span> <span class="nx">HttpUri</span><span class="o">::</span><span class="na">createFromString</span><span class="p">(</span><span class="nv">$base_uri</span><span class="p">);</span>
<span class="nv">$modifier</span> <span class="o">=</span> <span class="k">new</span> <span class="nx">MergeQuery</span><span class="p">(</span><span class="nv">$query_to_merge</span><span class="p">);</span>

<span class="nv">$new_uri</span> <span class="o">=</span> <span class="nv">$modifier</span><span class="o">-&gt;</span><span class="na">process</span><span class="p">(</span><span class="nv">$uri</span><span class="p">);</span>
<span class="k">echo</span> <span class="nv">$new_uri</span><span class="p">;</span>
<span class="c1">// display http://www.example.com?fo.o=bar&amp;taz=#~typo</span></code></pre>
</div>
    </div>
  </div>

  <div class="highlights">
    <div class="inner-content">
      <div class="column one">
        <h1>Highlights</h1>
        <div class="description">
        <p>The library provides simple and intuitive classes to parse, validate, format and manipulate URIs and their components. It is built to enable working with
        any kind of RFC3986 compliant URI through extensions and middlewares.</p>
        </div>
      </div>
      <div class="column two">
        <ol>
          <li><p>Simple and extensible API</p></li>
          <li><p><a href="http://tools.ietf.org/html/rfc3986">RFC3986</a> compliant</p></li>
          <li><p>Implements <a href="http://www.php-fig.org/psr/psr-7/">PSR-7</a> <code>UriInterface</code> interface</p></li>
          <li><p>Framework-agnostic</p></li>
        </ol>
      </div>
    </div>
  </div>
  <div class="documentation">
    <div class="inner-content">
      <h1>Releases</h1>
      <div class="version current">
        <h2>Current Stable Release</h2>
        <div class="content">
          <p><code>League\Uri 5.0</code></p>
          <ul>
            <li>Requires: <strong>PHP >= 7.0.0</strong></li>
            <li>Release Date: <strong>2017-02-06</strong></li>
            <li>Supported Until: <strong>TBD</strong></li>
          </ul>
          <p><a href="/5.0/">Full Documentation</a></p>
        </div>
      </div>
      <div class="version security">
        <h2>Old stable release</h2>
        <div class="content">
          <p><code>League\Uri 4.0</code></p>
          <ul>
            <li>Requires: <strong>PHP >= 5.5.9</strong></li>
            <li>Release Date: <strong>2015-09-23</strong></li>
            <li>Supported Until: <strong>2017-08-06</strong></li>
          </ul>
          <p><a href="/4.0/">Full Documentation</a></p>
        </div>
      </div>

      <p class="footnote">Once a new major version is released, the previous stable release remains supported for six (6) more months through patches and/or security fixes.</p>

    </div>
  </div>
  <div class="questions">
    <div class="inner-content">
      <h1>Questions?</h1>
      <p><strong>League\Uri</strong> was created by Ignace Nyamagana Butera. Find him on Twitter at <a href="https://twitter.com/nyamsprod">@nyamsprod</a>.</p>
    </div>
  </div>
</main>