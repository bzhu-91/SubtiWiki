<form class="box">
	<h3><b>Filters</b></h3>
	<input type="checkbox" name="filters" style="display: none;" checked>
	<p style="line-height: 2em">By target: 
		<span class="checkbox-span">
			<input type="checkbox" name="filter-gene" id="filter-gene" />
			<label for="filter-gene">Gene</label>
		</span>
		<span class="checkbox-span">
			<input type="checkbox" name="filter-operon" id="filter-operon" />
			<label for="filter-operon">Operon</label>
		</span>
		<span class="checkbox-span">
			<input type="checkbox" name="filter-regulon" id="filter-regulon" />
			<label for="filter-regulon">Regulon</label>
		</span>
		<span class="checkbox-span">
			<input type="checkbox" name="filter-regulation" id="filter-regulation" />
			<label for="filter-regulation">Regulation</label>
		</span>
		<span class="checkbox-span">
			<input type="checkbox" name="filter-category" id="filter-category" />
			<label for="filter-category">Category</label>
		</span>
		<span class="checkbox-span">
			<input type="checkbox" name="filter-geneCategory" id="filter-geneCategory" />
			<label for="filter-geneCategory">Gene's category</label>
		</span>
		<span class="checkbox-span">
			<input type="checkbox" name="filter-interaction" id="filter-interaction" />
			<label for="filter-interaction">Interaction</label>
		</span>
		<span class="checkbox-span">
			<input type="checkbox" name="filter-paralogousProtein" id="filter-paralogousProtein" />
			<label>Paralogous proteins</label>
		</span>
	</p>
	<p>By operation:
		<span class="checkbox-span">
			<input type="checkbox" name="operation-add" id="operation-add" />
			<label for="operation-add">Add</label>
		</span>
		<span class="checkbox-span">
			<input type="checkbox" name="operation-update" id="operation-update" />
			<label for="operation-update">Update</label>
		</span>
		<span>
			<input type="checkbox" name="operation-remove" id="operation-remove" />
			<label for="operation-remove">Remove</label>
		</span>
	</p>
	<p>By user: <select id="users" name="user"></select></p>
	<input type="hidden" name="page" value="1" />
	<input type="hidden" name="page_size" value="{{:page_size}}" />
	<p style="text-align: right;">
		<a class="button" style="float: left;margin: 0" id="clear-all">Clear all</a>
		<a class="button" style="float: left;" id="check-all">Check all</a>
		<input type="submit" />
	</p>
	<p style="clear: both;"></p>
</form>
{{table:history}}
{{:message}}
<p>
	<a href="" id="previous" class="button" >Previous</a>
	<select id="select-page" style="position: relative; left: 35%"></select>
	<a href="" id="next" class="button" style="float: right;">Next</a>
</p>
{{jsvars:vars}}