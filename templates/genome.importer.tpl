<div style="color:red">{{list:errors}}</div>
<div>
    This interface is for database administrators to import genomic context data.
    <h3>File format:</h3>
    <ul>
        <li>A tabular file</li>
        <li>"\t" as field delimiter and "\n" as line delimiter</li>
        <li>Headers are required</li>
    </ul>
    <h3>Modes: </h3>
    <ul>
        <li>replace: the content of GenomicContext table will be replaced by the content of uploaded file</li>
        <li>append: add new rows to the GenomicContext table with the content of the uploaded file</li>
    </ul>
    <h3>Required columns</h3>
    <h4>For genes:</h4>
    <ul>
        <li>start: the starting point of the genome element</li>
        <li>stop: the stoping point of the genome element</li>
        <li>strand: the strand where the genome element is, should be 1 (for plus strand) or 0 (for minus strand)</li>
        <li>locus: the locus of the gene</li>
    </ul>
    <h4>For upshift/downshift/transcription start site</h4>
    <ul>
        <li>position: the position on the genome</li>
        <li>strand: the strand, should be 1 (for plus strand) or 0 (for minus strand)</li>
        <li>object: should be upshift/downshift/TSS (for transcription start site)</li>
    </ul>
    <h3>Optional columns</h3>
    <ul>
        <li>strain: if multiple strains exists in the database</li>
    </ul>
    <p><i>Hint: the genome browser on the gene pages, expression browser page and genome browser page is able to display genes, upshifts, downshifts and TSS. For other type of data, an extension of genome browser is needed for proper display of the data. This importer does not restrict the data type</i></p>
</div>
<form method="post" enctype="multipart/form-data" class="box">
    <p><label>File: </label><input type="file" name="file" /><span>Max. 2MB</span></p>
    <p><label>Mode: </label>
        <input type="radio" name="mode" value="replace" /><span>Replace</span>
        <input type="radio" name="mode" value="append" checked/><span>Append</span>
    </p>
    <p style="text-align:right">
        <input type="submit" />
    </p>
</form>