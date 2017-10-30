---
layout: default
title: URI helpers cheat sheet
---

# Functions Cheat Sheet

## Introduction

While this library ships with a numbers of classes to ease URI manipulations, it also includes a variety of functions in the `League\Uri` namespace. Those functions work as aliases to most of the package classes to ease URI manipulation by reducing boilerplate code.  
Because these functions require different URI packages. Some of them may be missing if you dont install their respective package.

## Available functions

<div class="functions-listing-container">
	<div class="functions-listing">
		<h3>URI informations</h3>
		<ul>
			<li><a href="/5.0/parser/parser/#scheme-validation">is_scheme</a></li>
			<li><a href="/5.0/parser/parser/#host-validation">is_host</a></li>
			<li><a href="/5.0/parser/parser/#port-validation">is_port</a></li>
			<li><a href="/5.0/manipulations/references/#isabsolute">is_absolute</a></li>
			<li><a href="/5.0/manipulations/references/#isnetworkpath">is_network_path</a></li>
			<li><a href="/5.0/manipulations/references/#isabsolutepath">is_absolute_path</a></li>
			<li><a href="/5.0/manipulations/references/#isrelativepath">is_relative_path</a></li>
			<li><a href="/5.0/manipulations/references/#issamedocument">is_same_document</a></li>
			<li><a href="/5.0/manipulations/references/#urireference">uri_reference</a></li>
		</ul>
	</div>
	<div class="functions-listing">
		<h3>Manipulating the complete URI</h3>
		<ul>
			<li><a href="/5.0/parser/parser/#uri-parsing">parse</a></li>
			<li><a href="/5.0/parser/builder/">build</a></li>
			<li><a href="/5.0/manipulations/formatter/#function-alias">uri_to_rfc3986</a></li>
			<li><a href="/5.0/manipulations/formatter/#function-alias">uri_to_rfc3987</a></li>
			<li><a href="/5.0/manipulations/middlewares/#uri-comparison">normalize</a></li>
			<li><a href="/5.0/manipulations/middlewares/#rrelativize-an-uri">relativize</a></li>
			<li><a href="/5.0/manipulations/middlewares/#resolving-a-relative-uri">resolve</a></li>
		</ul>
	</div>
	<div class="functions-listing">
		<h3>Manipulating the URI query string</h3>
		<ul>
			<li><a href="/5.0/components/query/#queryextract">parse_query</a></li>
			<li><a href="/5.0/manipulations/middlewares/#merging-query-string">merge_query</a></li>
			<li><a href="/5.0/manipulations/middlewares/#append-data-to-the-query-string">append_query</a></li>
			<li><a href="/5.0/manipulations/middlewares/#removing-query-pairs">remove_pairs</a></li>
			<li><a href="/5.0/manipulations/middlewares/#sorting-the-query-keys">sort_query</a></li>
		</ul>
	</div>
	<div class="functions-listing">
		<h3>Manipulating the URI host</h3>
		<ul>
			<li><a href="/5.0/manipulations/middlewares/#transcoding-the-host-to-ascii">host_to_ascii</a></li>
			<li><a href="/5.0/manipulations/middlewares/#transcoding-the-host-to-its-idn-form">host_to_unicode</a></li>
			<li><a href="/5.0/manipulations/middlewares/#updating-the-host-registrable-domain">replace_registrabledomain</a></li>
			<li><a href="/5.0/manipulations/middlewares/#updating-the-host-subdomain">replace_subdomain</a></li>
			<li><a href="/5.0/manipulations/middlewares/#adding-the-root-label">add_root_label</a></li>
			<li><a href="/5.0/manipulations/middlewares/#removing-the-root-label">remove_root_label</a></li>
			<li><a href="/5.0/manipulations/middlewares/#appending-labels">append_host</a></li>
			<li><a href="/5.0/manipulations/middlewares/#prepending-labels">prepend_host</a></li>
			<li><a href="/5.0/manipulations/middlewares/#removing-selected-labels">remove_labels</a></li>
			<li><a href="/5.0/manipulations/middlewares/#replacing-host-label">replace_label</a></li>
			<li><a href="/5.0/manipulations/middlewares/#removing-zone-identifier">remove_zone_id</a></li>
		</ul>
	</div>
	<div class="functions-listing">
		<h3>Manipulating the URI path</h3>
		<ul>
			<li><a href="/5.0/manipulations/middlewares/#adding-leading-slash">add_leading_slash</a></li>
			<li><a href="/5.0/manipulations/middlewares/#removing-leading-slash">remove_leading_slash</a></li>
			<li><a href="/5.0/manipulations/middlewares/#adding-trailing-slash">add_trailing_slash</a></li>
			<li><a href="/5.0/manipulations/middlewares/#removing-trailing-slash">remove_trailing_slash</a></li>
			<li><a href="/5.0/manipulations/middlewares/#add-the-path-basepath">add_basepath</a></li>
			<li><a href="/5.0/manipulations/middlewares/#remove-the-path-basepath">remove_basepath</a></li>
			<li><a href="/5.0/manipulations/middlewares/#prepeding-segments">prepend_path</a></li>
			<li><a href="/5.0/manipulations/middlewares/#appending-path">append_path</a></li>
			<li><a href="/5.0/manipulations/middlewares/#transcoding-data-uri-from-binary-to-ascii">path_to_ascii</a></li>
			<li><a href="/5.0/manipulations/middlewares/#transcoding-data-uri-from-ascii-to-binary">path_to_binary</a></li>
			<li><a href="/5.0/manipulations/middlewares/#removing-dot-segments">remove_dot_segments</a></li>
			<li><a href="/5.0/manipulations/middlewares/#removing-empty-segments">remove_empty_segments</a></li>
			<li><a href="/5.0/manipulations/middlewares/#removing-selected-segments">remove_segments</a></li>
			<li><a href="/5.0/manipulations/middlewares/#updating-path-basename">replace_basename</a></li>
			<li><a href="/5.0/manipulations/middlewares/#updating-path-dirname">replace_dirname</a></li>
			<li><a href="/5.0/manipulations/middlewares/#updating-path-extension">replace_extension</a></li>
			<li><a href="/5.0/manipulations/middlewares/#replacing-a-path-segment">replace_segment</a></li>
			<li><a href="/5.0/manipulations/middlewares/#update-data-uri-parameters">replace_data_uri_parameters</a></li>
		</ul>
	</div>
</div>