import { Component, OnInit } from '@angular/core';
import { INgxMyDpOptions } from 'ngx-mydatepicker';
import { AdminService } from '../../../../services/admin.service';
import { AdminLogging } from '../../../../../entities/models-admin';
import { ToastrService } from 'ngx-toastr';
import { SharedService } from '../../../../services/shared.service';

@Component({
  selector: 'app-admin-activity',
  templateUrl: './admin-activity.component.html',
  styleUrls: ['./admin-activity.component.scss']
})
export class AdminActivityComponent implements OnInit {

  public myOptionsDate: INgxMyDpOptions = { dateFormat: 'mm.dd.yyyy' };
  public model: any = { date: { year: 2018, month: 10, day: 9 } };

  public preloaderPage = true;
  public loggingList = Array<AdminLogging>();

  public paginationLoader = false;
  public pagination = 1;
  public loadMoreCheck = true;

  constructor(
    private readonly _adminService: AdminService,
    private readonly _toastr: ToastrService,
    private readonly _sharedService: SharedService
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.getAdminLogging('', '', '', this.pagination);
  }

  /**
   * Reset Array
   */
  public resetArrayPagination(): void{
    this.loggingList = [];
    this.pagination = 1;
  }

  /**
   * Load pagination
   */
  public loadPagination(): void {
    this.pagination++;
    this.paginationLoader = true;
    this.getAdminLogging('', '', '', this.pagination);
  }

  /**
   * Get admin loggings
   * @param search
   * @param startD
   * @param endD
   * @param page
   * @return {Promise<void>}
   */
  public async getAdminLogging(search, startD, endD, page): Promise<void> {
    const startDate = new Date(startD);
    const endDate = new Date(endD);
    if (startDate > endDate) {
      this._toastr.error('Date End not be shorter than the Date Start');
    }
    else{
      try {
        const start = (startD !== '') ? startDate.getDate() + '.' + (startDate.getMonth() + 1) + '.' + startDate.getFullYear() : startD;
        const end = (endD !== '') ? endDate.getDate() + '.' + (endDate.getMonth() + 1) + '.' + endDate.getFullYear() : endD;
        const response = await this._adminService.getAdminLogging(search, start, end, page);

        response.items.forEach((item) => {
          this.loggingList.push(item);
        });

        if (response.pagination.total_count === this.loggingList.length) {
          this.loadMoreCheck = false;
        }
        else if (response.pagination.total_count !== this.loggingList.length) {
          this.loadMoreCheck = true;
        }
        this.paginationLoader = false;

        this.preloaderPage = false;
      }
      catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

}
