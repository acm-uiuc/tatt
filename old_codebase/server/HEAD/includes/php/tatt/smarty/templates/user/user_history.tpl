{*
The MIT License

Copyright (c) 2011 Nick Tabick

Permission is hereby granted, free of charge, to any person obtaining a copy
f this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

Smarty is licensed under the GNU LESSER GENERAL PUBLIC LICENSE
http://www.gnu.org/licenses/lgpl-3.0.txt
			    V-- Tab to here *}
{include file="components/start-content.tpl"}
				<h2>Currently Checked Out By Me</h2>
				<table class="table_items">
					<thead>
						<tr>
							<th>Name</th>
							<th>Type</th>
							<th>Location</th>
							<th>Last Accounted For</th>
							<th>On Loan To</th>
						</tr>
					</thead>
					<tfoot>
					</tfoot>
					<tbody>
						{foreach $checkedoutitems as $item}
						<tr>
							<td><a href="/items/item.php?id={$item['id']}">{$item['name']}</a></td>
							<td>{$item['type_name']}</td>
							<td>{if $item['location'] != NULL}{$item['location']}{else}--{/if}</td>
							<td>{$item['last_accounted_for']|date_format}</td>
							<td>{if $item['checked_out_by'] != NULL } {$item['checked_out_by']['username']} {else}--{/if} </td>
						</tr>
						{/foreach}
					</tbody>
				</table>

{include file="components/content-end.tpl"}
