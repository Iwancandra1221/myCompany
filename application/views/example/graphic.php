<script src="<?php echo base_url(); ?>js/vendor/jchartfx/js/jchartfx.full.js"></script>
<script src="<?php echo base_url(); ?>js/vendor/jchartfx/js/jchartfx.animation.js"></script>
<script>
$(document).ready(function(){
    var chart1;
    var items1 = <?php echo json_encode($array1); ?>;
    var td1;
    td1 = new cfx.TitleDockable();
    td1.setDock(cfx.DockArea.Bottom);
    td1.setText("By Journal Type");
    chart1 = new cfx.Chart();
    chart1.getTitles().add(td1);
    chart1.getLegendBox().setDock(cfx.DockArea.Bottom);
    chart1.setGallery(cfx.Gallery.Pie);
    chart1.setDataSource(items1);
    chart1.getAllSeries().getPointLabels().setVisible(true);
    chart1.create(document.getElementById('chart1'));


    var items2 = <?php echo json_encode($array2); ?>;

    chart2 = new cfx.Chart();
    //chart2.setGallery(cfx.Gallery.Bar);
    chart2.getAnimations().getLoad().setEnabled(true);
    chart2.getAxisY().getGrids().setInterlaced(true);
    chart2.getAxisY().getGrids().getMajor().setTickMark(cfx.TickMark.None);
    chart2.setDataSource(items2);
    chart2.create(document.getElementById('chart2'));

    var items3 = <?php echo json_encode($array2); ?>;

    chart3 = new cfx.Chart();
    chart3.setGallery(cfx.Gallery.Bar);
    chart3.getAnimations().getLoad().setEnabled(true);
    chart3.getAxisY().getGrids().setInterlaced(true);
    chart3.getAxisY().getGrids().getMajor().setTickMark(cfx.TickMark.None);
    chart3.setDataSource(items3);
    chart3.create(document.getElementById('chart3'));

    var items4 = <?php echo json_encode($array3); ?>;

    chart4 = new cfx.Chart();
    chart4.setGallery(cfx.Gallery.Bar);
    chart4.getAllSeries().setStacked(cfx.Stacked.Normal);
    chart4.getAnimations().getLoad().setEnabled(true);
    chart4.getAxisY().getGrids().setInterlaced(true);
    chart4.getAxisY().getGrids().getMajor().setTickMark(cfx.TickMark.None);
    chart4.setDataSource(items4);
    chart4.create(document.getElementById('chart4'));
});
</script>
<style>
#chart1
{
	height: 400px;
	width: 400px;
}
#chart2
{
	height: 400px;
	width: 600px;
}
#chart3
{
    height: 400px;
    width: 600px;
}
#chart4
{
    height: 400px;
    width: 600px;
}

.jchartfx #C1s g
{
	display: none;
}
</style>
<div id="chart1">
</div>
<div id="chart2">
</div>
<div id="chart3">
</div>
<div id="chart4">
</div>