import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../services/shared.service';
import { ToastrService } from 'ngx-toastr';
import { AdminService } from '../../../../services/admin.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { BusinessJobsAwaitingApproval } from '../../../../../entities/models';
import { INgxMyDpOptions } from 'ngx-mydatepicker';
import { Angular5Csv } from 'angular5-csv/Angular5-csv';

@Component({
  selector: 'app-admin-all-jobs',
  templateUrl: './admin-all-jobs.component.html',
  styleUrls: ['./admin-all-jobs.component.scss']
})
export class AdminAllJobsComponent implements OnInit {

  public jobsAwaitingApprove = Array<BusinessJobsAwaitingApproval>();

  public selectedBusinessJobId: number;
  public modalActiveClose: any;

  public myOptionsDate: INgxMyDpOptions = { dateFormat: 'dd.mm.yyyy',
    dayLabels: {su: 'S', mo: 'M', tu: 'T', we: 'W', th: 'T', fr: 'F', sa: 'S'},
    monthLabels: {1: 'January', 2: 'February', 3: 'March', 4: 'April', 5: 'May', 6: 'June', 7: 'July', 8: 'August', 9: 'September', 10: 'October', 11: 'November', 12: 'December'}};
  public model: any = { date: { year: 2018, month: 10, day: 9 } };

  public preloaderPage = true;

  public paginationLoader = false;
  public pagination = 1;
  public loadMoreCheck = true;

  public selectedBusinessJob: any;
  public selectedBusinessJobArray: any;
  public selectedBusinessJobStatus: boolean;

  constructor(
    private readonly _modalService: NgbModal,
    private readonly _adminService: AdminService,
    private readonly _toastr: ToastrService,
    private readonly _sharedService: SharedService
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.getAllJobs('', true, '', '', false);
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param job {object} - job id to be used for fetching data and showing in popup
   * @param data {array} - job id to be used for fetching data and showing in popup
   * @param status {boolean} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCenterJob(content: any,  job, data, status): void {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.selectedBusinessJob = job;
    this.selectedBusinessJobArray = data;
    this.selectedBusinessJobStatus = status;
  }

  /**
   * Export CSV file
   * @return {Promise<void>}
   */
  public async exportDataCSV(search, status, dateStart, dateEnd): Promise<void>{

    const data = {
      search: search,
      status: String(status),
      dateStart: dateStart,
      dateEnd: dateEnd,
      page: 1,
      csv: true
    };

    try {
      if (dateStart && dateEnd && dateStart > dateEnd ) {
        this._toastr.error('Date End not be shorter than the Date Start');
      }
      else {
        const response = await this._adminService.getAllJobs(data);

        const options = {
          showLabels: true,
          headers: ['Date', 'Contact', 'Email', 'Phone', 'Company', 'Job Title', 'Active']
        };

        new Angular5Csv(response, 'All jobs', options);
      }
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Reset Array
   */
  public resetArrayPagination(): void{
    this.jobsAwaitingApprove = [];
    this.pagination = 1;
  }

  /**
   * Load pagination
   */
  public async loadPagination(search, status, dateStart, dateEnd): Promise<void> {
    if (status === undefined || status === null) {
      status = true;
    }

    this.pagination++;
    this.paginationLoader = true;
    this.getAllJobs(search, status, dateStart, dateEnd, false);
  }

  /**
   * Get all jobs
   * @return {Promise<void>}
   */
  public async getAllJobs(search, status, dateStart, dateEnd, csv): Promise<void> {
    status = Boolean(status);
    const data = {
      search: search,
      status: String(status),
      dateStart: dateStart,
      dateEnd: dateEnd,
      page: this.pagination,
      csv: csv
    };

      if (dateStart && dateEnd && dateStart > dateEnd ) {
        this._toastr.error('Date End not be shorter than the Date Start');
      }
      else {
        try {
          const response = await this._adminService.getAllJobs(data);

          response.items.forEach((item) => {
            this.jobsAwaitingApprove.push(item);
          });

          if (response.pagination.total_count === this.jobsAwaitingApprove.length) {
            this.loadMoreCheck = false;
          }
          else if (response.pagination.total_count !== this.jobsAwaitingApprove.length){
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

  /**
   * changes status for hob specified with id
   * @param id {number} - id of the job
   * @param status {boolean} - status of the job - true - approve, false - decline
   */
  public async approveJob(id: number, status: boolean): Promise<void> {
    try {
      await this._adminService.changeJobsStatus(id, { approve: status });
      const notificationMessage = (status) ? 'Job has been approved!' : 'Job has been declined!';
      this._toastr.success(notificationMessage);
      this.jobsAwaitingApprove = this.jobsAwaitingApprove.filter((job) => job.id !== id);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete jobs
   * @param id {number}
   * @return {Promise<void>}
   */
  public async deleteJobs(id: number): Promise<void> {
    try {
      await this._adminService.deleteJobs(id);
      this.jobsAwaitingApprove = this.jobsAwaitingApprove.filter((listElement) => listElement.id !== id);
      this.modalActiveClose.dismiss();
      this._sharedService.sidebarAdminBadges.jobAll--;
      this._toastr.success('Job has been closed');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update status job for admin
   * @param id {number}
   * @param status {boolean}
   * @return {Promise<void>}
   */
  public async updateJobStatus(id: number, status: boolean): Promise<void> {
    status = !status;
    try {
      await this._adminService.updateJobStatus(id, status);
      this.jobsAwaitingApprove = this.jobsAwaitingApprove.filter((listElement) => listElement.id !== id);
      this._toastr.success('Job status has been changed');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param jobId {number} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCentered(content: any,  jobId: number) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.selectedBusinessJobId = jobId;
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   */
  public openVerticallyCenter(content: any) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'sm' });
  }

}
