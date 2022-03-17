<div class="language-php highlighter-rouge"><div class="highlight"><pre class="highlight"><code><span class="k">use</span> <span class="nx">League\Uri\Components\Query</span><span class="p">;</span>
<span class="k">use</span> <span class="nx">League\Uri\Uri</span><span class="p">;</span>
<span class="k">use</span> <span class="nx">League\Uri\UriModifier</span><span class="p">;</span>

<span class="nv">$uri</span> <span class="o">=</span> <span class="nx">Uri</span><span class="o">::</span><span class="na">createFromString</span><span class="p">(</span><span class="s1">'http://example.com?q=value#fragment'</span><span class="p">);</span>
<span class="nv">$uri</span><span class="o">-&gt;</span><span class="na">getScheme</span><span class="p">();</span> <span class="c1">// returns 'http'</span>
<span class="nv">$uri</span><span class="o">-&gt;</span><span class="na">getHost</span><span class="p">();</span>   <span class="c1">// returns 'example.com'</span>

<span class="nv">$newUri</span> <span class="o">=</span> <span class="nx">UriModifier</span><span class="o">::</span><span class="na">appendQuery</span><span class="p">(</span><span class="nv">$uri</span><span class="p">,</span> <span class="s1">'q=new.Value'</span><span class="p">);</span>
<span class="k">echo</span> <span class="nv">$newUri</span><span class="p">;</span> <span class="c1">// 'http://example.com?q=value&amp;q=new.Value#fragment'</span>

<span class="nv">$query</span> <span class="o">=</span> <span class="nx">Query</span><span class="o">::</span><span class="na">createFromUri</span><span class="p">(</span><span class="nv">$newUri</span><span class="p">);</span>
<span class="nv">$query</span><span class="o">-&gt;</span><span class="na">get</span><span class="p">(</span><span class="s1">'q'</span><span class="p">);</span>    <span class="c1">// returns 'value'</span>
<span class="nv">$query</span><span class="o">-&gt;</span><span class="na">getAll</span><span class="p">(</span><span class="s1">'q'</span><span class="p">);</span> <span class="c1">// returns ['value', 'new.Value']</span>
<span class="nv">$query</span><span class="o">-&gt;</span><span class="na">params</span><span class="p">(</span><span class="s1">'q'</span><span class="p">);</span> <span class="c1">// returns 'new.Value'</span>
</code></pre></div></div>

<p>The libraries manipulate URIs and their components using a simple yet expressive code.</p>
