<!-- Пагинация НАД таблицей -->
            <div id="pager-top" class="pager" style="margin-bottom: 15px;">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-default first"><i class="glyphicon glyphicon-step-backward"></i> Первая</button>
                            <button type="button" class="btn btn-sm btn-default prev"><i class="glyphicon glyphicon-backward"></i> Назад</button>
                            <button type="button" class="btn btn-sm btn-default next">Вперед <i class="glyphicon glyphicon-forward"></i></button>
                            <button type="button" class="btn btn-sm btn-default last">Последняя <i class="glyphicon glyphicon-step-forward"></i></button>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 text-right">
                        <div class="pagination-info" style="display: inline-block; margin-right: 15px;">
                            <span class="pagedisplay"></span>
                        </div>
                        
                        <div class="pagination-size" style="display: inline-block;">
                            <label style="margin-right: 5px; font-weight: normal;">Показывать:</label>
                            <select class="pagesize form-control input-sm" style="width: auto; display: inline-block;">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                        
                        <div class="pagination-goto" style="display: inline-block; margin-left: 15px;">
                            <label style="margin-right: 5px; font-weight: normal;">Страница:</label>
                            <input type="text" class="pagenum form-control input-sm" size="4" style="width: 60px; display: inline-block;">
                        </div>
                    </div>
                </div>
            </div>