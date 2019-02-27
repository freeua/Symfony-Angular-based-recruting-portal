import { Component, OnInit } from '@angular/core';
import { BusinessJobsAwaitingApproval } from '../../../../../entities/models';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AdminService } from '../../../../services/admin.service';
import { ToastrService } from 'ngx-toastr';
import { SharedService } from '../../../../services/shared.service';

@Component({
  selector: 'app-admin-new-jobs',
  templateUrl: './admin-new-jobs.component.html',
  styleUrls: ['./admin-new-jobs.component.scss']
})
export class AdminNewJobsComponent implements OnInit {

  public jobsAwaitingApprove = Array<BusinessJobsAwaitingApproval>();

  public selectedBusinessJobId: number;
  public modalActiveClose: any;
  public preloaderPage = true;

  public paginationLoader = false;
  public pagination = 1;
  public loadMoreCheck = true;

  public confirmFunction: string;
  public confirmData: any;
  public confirmStatus: any;
  public confirmArray: any;

  public selectedBusinessJob: any;
  public selectedBusinessJobArray: any;
  public selectedBusinessJobStatus: boolean;

  constructor(
    private readonly _modalService: NgbModal,
    private readonly _adminService: AdminService,
    private readonly _sharedService: SharedService
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.getAllJobsAwaitingApprove();
  }

  /**
   * Open confirm popup
   * @param content
   * @param confirmArray
   * @param nameFunction
   * @param data
   * @param status
   */
  public openConfirm(content: any, confirmArray, nameFunction, data, status): void {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'sm', windowClass: 'width-min' });
    this.confirmFunction = nameFunction;
    this.confirmData = data;
    this.confirmStatus = status;
    this.confirmArray = confirmArray;
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
   * Load pagination
   */
  public async loadPagination(): Promise<void> {

    this.pagination++;
    this.paginationLoader = true;
    this.getAllJobsAwaitingApprove();
  }

  /**
   * gets the list of all jobs awaiting approve
   */
  public async getAllJobsAwaitingApprove(): Promise<void> {
    try {
      const response = await this._adminService.getAdminJobsApproved(this.pagination);

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

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param jobId {number} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCentered(content: any,  jobId: number) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.selectedBusinessJobId = jobId;
  }

}
