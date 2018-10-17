<h2><a href="gene?id={{:id}}">{{:title}}</a></h2>
<div>
{{::rest}}
<p class='m_value_inline'>
	<a class='inner-block' href='gene?id={{:id}}' target='_blank'><i>Listi</i>Wiki</a>
	<a class='inner-block' href='interaction?gene={{:id}}' target='_blank'><i>List</i>Interact</a>	
	<a class='inner-block' href='javascript: pathwaySearch("{{:id}}")' target='_blank'><i>Listi</i>Pathways</a>
</p>
<div style="display: none;">
	<p>{{:bank_id}}</p>
	<p>{{:lastUpdate}}</p>
	<p>{{:lastAuthor}}</p>
	<p>{{:count}}</p>
</div>
</div>