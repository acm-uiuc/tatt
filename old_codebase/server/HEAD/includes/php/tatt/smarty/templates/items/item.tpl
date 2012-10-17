{*
The MIT License

Copyright (c) 2011 Eric Parsons

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
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
			<div id="item_details">
				<strong>Name:</strong> {$item['name']} <br />
				<strong>Owner:</strong> {$item['owner']['username']} <br />
				<strong>Type:</strong> {$item['type_name']} <br />
				<strong>Location:</strong> {if $item['location'] != NULL}{$item['location']}{else}<em>Not set</em>{/if} <br />
				<strong>Last Accounted For:</strong> {$item['last_accounted_for']|date_format} <br />
				{if $item['checked_out_by'] != NULL}<strong>On Loan To:</strong> {$item['checked_out_by']['username']} <br />{/if}
				{if $item['attributes'] != NULL}
				{foreach $item['attributes'] as $attribute}
					<strong>{$attribute['name']|capitalize}:</strong> {$attribute['value']} <br />
				{/foreach}
				{/if} <br />
				{if $logged_in && $item['owner_id'] == $user_id}
					<a href="/items/edit_item.php?id={$item['id']}">Edit Item</a> | 
					<a href="/items/item.php?id={$item['id']}&action=delete" class="confirm">Delete Item</a> | 
					<a href="/items/item.php?id={$item['id']}&action=checkout">Checkout Item</a> | 
					<a href="/items/item.php?id={$item['id']}&action=return">Return Item</a>
				{/if}
			</div>
			<div id="item_qr">
				<a href="/qrcodes/{$item['id']}.png">
					<img src="/qrcodes/{$item['id']}.png" />
				</a>
			</div>

{include file="components/content-end.tpl"}
