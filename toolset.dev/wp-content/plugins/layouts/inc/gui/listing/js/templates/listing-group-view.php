<script type="text/html" id="table-listing-group">
	<# if( ddl.status == 'publish' ){ #>
	<tr class="ddl-groups-heading js-ddl-groups-heading">
		<td></td>
		<td colspan="4" class="post-title page-title column-title">
			<div class="listing-heading-inner-wrap">
				<i class="icon-caret-up js-collapse-group"></i>
				<span class="group-name">{{{ ddl.name }}} ({{{ ddl.how_many }}})</span>
			</div>
		</td>
	</tr>
	<# } #>
</script>

