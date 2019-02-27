import { Component, OnInit } from '@angular/core';
import { AdminInterviewList } from '../../../../../entities/models-admin';
import { SharedService } from '../../../../services/shared.service';
import { AdminService } from '../../../../services/admin.service';
import { Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-successfull-placed',
  templateUrl: './admin-successfull-placed.component.html',
  styleUrls: ['./admin-successfull-placed.component.scss']
})
export class AdminSuccessfullPlacedComponent implements OnInit {

  public successfulPlacedInterviews = Array<AdminInterviewList>();

  public preloaderPage = true;
  public totalCount: number;

  public paginationLoader = false;
  public pagination = 1;
  public loadMoreCheck = true;
  public modalActiveClose: any;
  public selectedBusinessId;
  public selectedBusinessJob;

  constructor(
    private readonly _adminService: AdminService,
    private readonly _sharedService: SharedService,
    private readonly _router: Router,
    private readonly _modalService: NgbModal
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.successfulPlacedInterviews = [];
    this.getSuccessfulPlacedInterviews();
  }

  /**
   * Router admin for candidate on id
   * @param id
   */
  public routeCandidate(id) {
    this._router.navigate(['/admin/edit_candidate'], { queryParams: { candidateId: id} });
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param id {number} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCentered(content: any,  id: number) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.selectedBusinessId = id;
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param jobId {number} - job id to be used for fetching data and showing in popup
   * @param clientID {number} - job id to be used for fetching data and showing in popup
   * @param subcontent {any} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCenterJob(content: any, jobId, clientID, subcontent): void {
    if (jobId){
      this.selectedBusinessJob = {
        id: jobId
      };
      this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    } else {
      this.selectedBusinessId = clientID;
      this.modalActiveClose = this._modalService.open(subcontent, { centered: true, 'size': 'lg' });
    }
  }

  /**
   * Load pagination
   */
  public async loadPagination(): Promise<void> {

    this.pagination++;
    this.paginationLoader = true;
    this.getSuccessfulPlacedInterviews();
  }

  /**
   * Get successfull placed interviews list
   * @return {Promise<void>}
   */
  public async getSuccessfulPlacedInterviews(): Promise<void> {
    try {
      const response = await this._adminService.getSuccessfulPlacedInterviews(this.pagination);

      response.items.forEach((item) => {
        this.successfulPlacedInterviews.push(item);
      });

      if (response.pagination.total_count === this.successfulPlacedInterviews.length) {
        this.loadMoreCheck = false;
      }
      else if (response.pagination.total_count !== this.successfulPlacedInterviews.length){
        this.loadMoreCheck = true;
      }
      this.paginationLoader = false;

      this.totalCount = response.pagination.total_count;
      this.preloaderPage = false;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Select change router
   * @param url
   */
  public routerApplicants(url): void {
    this._router.navigate([url]);
  }


}
