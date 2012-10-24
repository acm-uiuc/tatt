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

                <form action="" method="post">
                    Name: <input type="text" name="name" {if isset($item['name']) && $item['name'] != NULL} value="{$item['name']}"{/if} /><br />
                    Location: <input type="text" name="location" {if isset($item['location']) && $item['location'] != NULL} value="{$item['location']}"{/if} /><br />
                    {foreach $attributes as $attribute}
                    <input type="hidden" name="attributes[{$attribute['attribute_id']}][id]" value="{$attribute['attribute_id']}" />
                    {$attribute['name']|capitalize}: <input type="text" name="attributes[{$attribute['attribute_id']}][value]" {if isset($attribute['value']) && $attribute['value'] != NULL} value="{$attribute['value']}"{/if} ><br />
                    {/foreach}
                    <input type="hidden" name="type_id" value="{$type_id}" />
                    <input type="submit" value="submit" />
                </form>

